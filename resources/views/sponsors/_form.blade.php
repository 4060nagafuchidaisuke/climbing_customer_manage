{{-- 登録画面（create）と編集画面（edit）で共通の入力欄 --}}
{{-- $sponsor：編集のときは対象の広告、登録のときは null --}}
{{-- 各項目のエラー：data-error-for の <p> に sponsor-form.js が書き込む。空のときは empty:hidden で隠れる --}}
@php($sponsor ??= null)

{{-- ファイルの選択 --}}
<label class="block text-sm font-medium text-gray-700 mb-1">
    画像・動画@if($sponsor)<span class="text-gray-500 text-xs">（差し替えるときだけ選んでください）</span>@endif
</label>
@if($sponsor)
    {{-- 今のファイル --}}
    <div class="mb-2">
        @if($sponsor->media_type === \App\Enums\SponsorMediaType::VIDEO)
            <video class="h-32" src="{{ $sponsor->media_url }}" controls></video>
        @elseif($sponsor->media_type === \App\Enums\SponsorMediaType::IMAGE)
            <img class="h-32" src="{{ $sponsor->media_url }}" alt="今の広告">
        @endif
    </div>
@endif
<input type="file" name="media"
        accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/webm">
<p class="text-gray-500 text-xs mt-1">50MBまで（画像：JPEG / PNG / WebP、動画：MP4 / MOV / AVI / WebM）</p>
<p data-error-for="media" class="text-red-500 text-xs mt-1 empty:hidden">@error('media'){{ $message }}@enderror</p>

{{-- スポンサー名 --}}
<label class="block text-sm font-medium text-gray-700 mb-1">登録スポンサー名</label>
<input type="text" name="title"
        value="{{ old('title', $sponsor?->title) }}"
        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                focus:ring-slate-500 focus:border-slate-500">
<p data-error-for="title" class="text-red-500 text-xs mt-1 empty:hidden">@error('title'){{ $message }}@enderror</p>

{{-- 表示秒数 --}}
<label class="block text-sm font-medium text-gray-700 mb-1">表示秒数<span class="text-gray-500 text-xs">（画像のみ。動画は最後まで再生します）</span></label>
<input type="number" name="display_seconds"
        value="{{ old('display_seconds', $sponsor?->display_seconds ?? 5) }}"
        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                focus:ring-slate-500 focus:border-slate-500">
<p data-error-for="display_seconds" class="text-red-500 text-xs mt-1 empty:hidden">@error('display_seconds'){{ $message }}@enderror</p>

{{-- 開始日 --}}
<label class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
<input type="date" name="start_date"
        value="{{ old('start_date', $sponsor?->start_date?->format('Y-m-d')) }}"
        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                focus:ring-slate-500 focus:border-slate-500">
<p data-error-for="start_date" class="text-red-500 text-xs mt-1 empty:hidden">@error('start_date'){{ $message }}@enderror</p>

{{-- 終了日 --}}
<label class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
<input type="date" name="end_date"
        value="{{ old('end_date', $sponsor?->end_date?->format('Y-m-d')) }}"
        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                focus:ring-slate-500 focus:border-slate-500">
<p data-error-for="end_date" class="text-red-500 text-xs mt-1 empty:hidden">@error('end_date'){{ $message }}@enderror</p>

{{-- 公開中かどうか --}}
{{-- エラーで戻ったとき（JS が動かない場合）は送った値を、そうでなければ今の設定を表示する --}}
<label class="block text-sm font-medium text-gray-700 mb-1">公開</label>
<input type="checkbox" name="is_active" value="1"
        @checked($errors->any() ? old('is_active') : ($sponsor?->is_active ?? false))
        class="rounded-md border-gray-300 shadow-sm text-sm
                focus:ring-slate-500 focus:border-slate-500">
<p data-error-for="is_active" class="text-red-500 text-xs mt-1 empty:hidden">@error('is_active'){{ $message }}@enderror</p>
