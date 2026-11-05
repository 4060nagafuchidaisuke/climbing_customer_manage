<?php

namespace App\Services;

use App\Enums\SponsorMediaType;
use App\Models\Sponsor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SponsorService
{
    /**
     * 広告を新規登録する（並び順は一番最後）
     *
     * @param  array{title: string, display_seconds: int, start_date: ?string, end_date: ?string, is_active: bool}  $data
     */
    public function create(array $data, UploadedFile $file): Sponsor
    {
        return Sponsor::create([
            ...$data,
            ...$this->storeMedia($file),
            'sort_order' => (Sponsor::max('sort_order') ?? 0) + 1,
        ]);
    }

    /**
     * 広告を更新する。ファイルが選ばれていれば差し替える
     *
     * @param  array{title: string, display_seconds: int, start_date: ?string, end_date: ?string, is_active: bool}  $data
     */
    public function update(Sponsor $sponsor, array $data, ?UploadedFile $file): Sponsor
    {
        $oldPath = $sponsor->media_path;

        if ($file) {
            // 新しいファイルを先に保存する（保存に失敗しても、古いファイルと DB はそのまま残る）
            $data = [...$data, ...$this->storeMedia($file)];
        }

        $sponsor->update($data);

        // DB を新しいファイルに書き換えられてから、古いファイルを消す
        if ($file) {
            Storage::disk('public')->delete($oldPath);
        }

        return $sponsor;
    }

    /**
     * 広告とファイルを削除する
     */
    public function delete(Sponsor $sponsor): void
    {
        Storage::disk('public')->delete($sponsor->media_path);
        $sponsor->delete();
    }

    /**
     * 並び順を1つ上（up）または1つ下（down）の広告と入れ替える
     * 番号の重なりや飛びが残らないよう、毎回 1, 2, 3… と振り直す
     */
    public function move(Sponsor $sponsor, string $direction): void
    {
        DB::transaction(function () use ($sponsor, $direction) {
            // 2人が同時に押しても番号が崩れないよう、並べ替え中は行をロックする
            $ids = Sponsor::ordered()->lockForUpdate()->pluck('id')->all();

            $from = array_search($sponsor->id, $ids, true);
            $to = $direction === 'up' ? $from - 1 : $from + 1;

            // 先頭の ↑・末尾の ↓ は何もしない
            if ($from === false || ! isset($ids[$to])) {
                return;
            }

            [$ids[$from], $ids[$to]] = [$ids[$to], $ids[$from]];

            foreach ($ids as $index => $id) {
                Sponsor::whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });
    }

    /**
     * ファイルを storage/app/public/sponsors/ に保存し、パスと種類（画像 or 動画）を返す
     *
     * @return array{media_path: string, media_type: SponsorMediaType}
     */
    private function storeMedia(UploadedFile $file): array
    {
        // getMimeType() は 'image/png' や 'video/mp4' のような文字列を返す
        $mediaType = str_starts_with($file->getMimeType(), 'video/')
            ? SponsorMediaType::VIDEO
            : SponsorMediaType::IMAGE;

        return [
            'media_path' => $file->store('sponsors', 'public'),
            'media_type' => $mediaType,
        ];
    }
}
