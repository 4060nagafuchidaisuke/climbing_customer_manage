#!/bin/bash
#
# HAZY BOULDER DB 復元（backup_db.sh で取ったバックアップを戻す）
#
# 使い方（プロジェクトのフォルダで）:
#   bash scripts/restore_db.sh                      … USB のバックアップ一覧から番号で選ぶ
#   bash scripts/restore_db.sh <ファイルのパス>      … 戻すファイルを直接指定する
#
# 流れ:
#   1. 戻すバックアップを選ぶ（ファイルが壊れていないかも確かめる）
#   2. 「yes」と打って確認する
#   3. 今の状態を backup_db.sh で自動バックアップする（失敗したら中止。間違えて戻してもやり直せるように）
#   4. 選んだバックアップを DB に流し込む（バックアップを取った時点の中身に置き換わる）
#   5. migrate（バックアップより後に増えた列やテーブルを足す）とキャッシュの作り直し
#
# 注意: 戻るのは DB だけ。スポンサー画像（storage/app/public）は戻らない
# macOS 標準の bash 3.2 でも動くように書いている（mapfile などは使わない）

# ---- 場所の自動判定（このスクリプト位置からプロジェクトを割り出す）----
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# ---- 設定（backup_db.sh と同じ）----
USB_ROOT="${BACKUP_USB_ROOT:-/Volumes/HazyUSB}"   # テスト時だけ環境変数で差し替え
BACKUP_DIR="$USB_ROOT/backups"
LIST_MAX=30
LOG="$HOME/backup_logs/backup.log"
SAIL="$PROJECT_DIR/vendor/bin/sail"

mkdir -p "$HOME/backup_logs"
log() { echo "$(date '+%Y-%m-%d %H:%M:%S') $1" >> "$LOG"; }
fail() {
  echo ""
  echo "✖ $1"
  log "復元を中止: $1"
  exit 1
}

cd "$PROJECT_DIR" || fail "プロジェクトフォルダに入れません: $PROJECT_DIR"

# ---- 1. 戻すバックアップを選ぶ ----
if [ -n "$1" ]; then
  FILE="$1"
else
  [ -d "$BACKUP_DIR" ] || fail "バックアップのフォルダが見つかりません: $BACKUP_DIR（USB が挿さっているか確認してください）"

  FILES=()
  while IFS= read -r f; do
    FILES+=("$f")
  done < <(ls -1t "$BACKUP_DIR"/hazy_*.sql.gz 2>/dev/null | head -n "$LIST_MAX")

  [ "${#FILES[@]}" -gt 0 ] || fail "バックアップが1つもありません: $BACKUP_DIR"

  echo "戻すバックアップを選んでください（新しい順）:"
  echo ""
  i=1
  for f in "${FILES[@]}"; do
    # hazy_20261116_2300.sql.gz → 2026/11/16 23:00
    name=$(basename "$f")
    stamp=${name#hazy_}
    stamp=${stamp%.sql.gz}
    when="${stamp:0:4}/${stamp:4:2}/${stamp:6:2} ${stamp:9:2}:${stamp:11:2}"
    size=$(du -h "$f" | cut -f1)
    printf "  %2d) %s に取ったもの  （%s, %s）\n" "$i" "$when" "$name" "$size"
    i=$((i + 1))
  done
  echo ""
  printf "番号を入力（やめるときは Enter だけ）: "
  read -r NUM

  [ -n "$NUM" ] || { echo "やめました。何も変更していません。"; exit 0; }
  case "$NUM" in
    *[!0-9]*) fail "番号は数字で入力してください: $NUM" ;;
  esac
  [ "$NUM" -ge 1 ] && [ "$NUM" -le "${#FILES[@]}" ] || fail "その番号はありません: $NUM"

  FILE="${FILES[$((NUM - 1))]}"
fi

[ -f "$FILE" ] || fail "ファイルが見つかりません: $FILE"
gunzip -t "$FILE" 2>/dev/null || fail "バックアップのファイルが壊れています（解凍できません）: $FILE"

# ---- 2. 確認 ----
echo ""
echo "次のバックアップの時点に DB を戻します:"
echo "  $FILE"
echo ""
echo "  ・今の会員・来店記録・売上などは、このバックアップの時点の中身に置き換わります"
echo "  ・戻す前に、今の状態を自動でバックアップします"
echo "  ・受付で誰も操作していないときに実行してください"
echo ""
printf "本当に戻しますか？ 戻すなら yes と入力: "
read -r ANSWER
[ "$ANSWER" = "yes" ] || { echo "やめました。何も変更していません。"; exit 0; }

log "=== 復元開始: $FILE ==="

# 選んだファイルを一時フォルダに写してから使う
# （次の自動バックアップは「分」単位の名前で、30世代を超えると古いものを消すので、
#   同じ分のファイルを上書きしたり、一番古いファイルを消したりしないように）
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT
cp "$FILE" "$WORK/restore.sql.gz" || fail "バックアップのファイルを読み込めません: $FILE"

# ---- 3. 今の状態をバックアップ ----
echo ""
echo "▶ 今の状態をバックアップしています…"
bash "$SCRIPT_DIR/backup_db.sh" || fail "今の状態のバックアップに失敗したので、戻すのをやめました（詳細は $LOG）"
echo "  $(grep 'バックアップ成功' "$LOG" | tail -n 1 | sed 's/^.*バックアップ成功: /保存先: /')"

# ---- 4. 流し込む ----
echo ""
echo "▶ バックアップを DB に戻しています…（数十秒かかることがあります）"
gunzip -c "$WORK/restore.sql.gz" \
  | docker compose exec -T mysql sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' 2>"$HOME/backup_logs/last_restore_error.txt"
STATUSES=("${PIPESTATUS[@]}")
if [ "${STATUSES[0]}" -ne 0 ] || [ "${STATUSES[1]}" -ne 0 ]; then
  log "詳細: $(cat "$HOME/backup_logs/last_restore_error.txt")"
  fail "DB に戻す途中で失敗しました。直前に取ったバックアップで元に戻せます（詳細は $LOG）"
fi
log "復元成功: $FILE"

# ---- 5. migrate とキャッシュ ----
echo ""
echo "▶ 足りない列やテーブルを追加しています（migrate）…"
"$SAIL" artisan migrate --force || fail "migrate に失敗しました。DB は戻っています。画面を確認してください"

echo ""
echo "▶ キャッシュを作り直しています…"
"$SAIL" artisan optimize:clear > /dev/null
"$SAIL" artisan config:cache > /dev/null
"$SAIL" artisan route:cache > /dev/null
"$SAIL" artisan view:cache > /dev/null

# ---- 結果 ----
echo ""
echo "✔ 復元が終わりました。"
"$SAIL" artisan tinker --execute="echo '  会員 ' . App\Models\Member::count() . '人 ／ 来店記録 ' . App\Models\Visit::count() . '件 ／ スタッフ ' . App\Models\Staff::count() . '人' . PHP_EOL;"
log "=== 復元終了 ==="
