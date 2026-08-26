#!/bin/bash
#
# HAZY BOULDER DB バックアップ
#

# ---- 場所の自動判定（このスクリプト位置からプロジェクトを割り出す）----
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# ---- 設定 ----
USB_ROOT="${BACKUP_USB_ROOT:-/Volumes/HazyUSB}"   # テスト時だけ環境変数で差し替え
BACKUP_DIR="$USB_ROOT/backups"
MARKER="$USB_ROOT/.hazy_backup_target"
KEEP=30
STAMP=$(date +%Y%m%d_%H%M)
LOG="$HOME/backup_logs/backup.log"
ERR="$HOME/backup_logs/last_dump_error.txt"

# ---- ログ関数 ----
mkdir -p "$HOME/backup_logs"
log() { echo "$(date '+%Y-%m-%d %H:%M:%S') $1" >> "$LOG"; }

log "=== バックアップ開始 ==="

# ---- マウント確認（命綱）----
if [ ! -f "$MARKER" ]; then
  log "USB未マウント（目印なし）のため中止"
  exit 1
fi
mkdir -p "$BACKUP_DIR"

# ---- dump → 仮ファイル ----
TMP="$BACKUP_DIR/hazy_${STAMP}.sql.gz.tmp"
FINAL="$BACKUP_DIR/hazy_${STAMP}.sql.gz"

cd "$PROJECT_DIR" || { log "プロジェクトフォルダに入れず中止"; exit 1; }

docker compose exec -T mysql sh -c 'mysqldump --single-transaction --no-tablespaces -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' 2>"$ERR" | gzip > "$TMP"

# ---- 成否チェック ----
if [ "${PIPESTATUS[0]}" -ne 0 ]; then
  log "mysqldump 失敗。中止"
  log "詳細: $(cat "$ERR")"
  rm -f "$TMP"
  exit 1
fi
if [ ! -s "$TMP" ]; then
  log "バックアップが空。中止"
  rm -f "$TMP"
  exit 1
fi

# ---- 合格 → 正式名に ----
mv "$TMP" "$FINAL"
log "バックアップ成功: $FINAL"

# ---- ローテーション（30世代）----
ls -1t "$BACKUP_DIR"/hazy_*.sql.gz 2>/dev/null | tail -n +$((KEEP + 1)) | while read -r f; do
  rm -f "$f"
  log "古いバックアップを削除: $f"
done

log "=== バックアップ終了 ==="