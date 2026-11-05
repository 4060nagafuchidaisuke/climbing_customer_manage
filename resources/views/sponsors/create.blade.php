<x-app-layout
    background="images/NewMember.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('sponsors.index') }}"
                   class="text-gray-400 hover:text-gray-600 text-sm">← 一覧に戻る</a>
                <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                    スポンサー登録（動画・静止画）
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- バリデーションエラー表示 --}}
            {{-- JS（sponsor-form.js）で送信したときは、JS がこの枠に書き込んで表示する --}}
            <div id="error-summary" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg {{ $errors->any() ? '' : 'hidden' }}">
                <p class="text-red-700 font-medium text-sm mb-2">入力内容を確認してください：</p>
                <ul class="text-red-600 text-sm list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

            {{-- 登録画面 --}}
            <div class="bg-white/80 rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">★スポンサー名の入力（<span class="text-red-500">*</span>は必須入力項目です）</h3>
                {{-- 本数は Sponsor::CHECKIN_LIMIT と連動（受付の処理と文言がずれないように） --}}
                <p class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-md text-amber-800 text-sm">
                    受付画面で再生される動画・静止画は、公開中かつ掲載期間内のもののうち、登録が古い順に<strong>上位{{ \App\Models\Sponsor::CHECKIN_LIMIT }}つまで</strong>です。
                </p>
                    <div class="grid grid-cols-2 gap-4">
                        {{-- スポンサー登録 --}}
                        <div class="col-span-2">
                            {{-- スポンサー名 --}}
                            {{-- id / data-max-mb は sponsor-form.js が使う。ページを読み込み直さずに送るので、エラーでもファイル選択が消えない --}}
                            <form id="sponsor-form" method="POST" action="{{ route('sponsors.store') }}" enctype="multipart/form-data"
                                  data-max-mb="50">
                            @csrf
                                {{-- 各項目のエラー：data-error-for の <p> に JS が書き込む。空のときは empty:hidden で隠れる --}}
                                {{-- ファイルの選択 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">画像・動画</label>
                                <input type="file" name="media"
                                        accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/webm">
                                <p class="text-gray-500 text-xs mt-1">50MBまで（画像：JPEG / PNG / WebP、動画：MP4 / MOV / AVI / WebM）</p>
                                <p data-error-for="media" class="text-red-500 text-xs mt-1 empty:hidden">@error('media'){{ $message }}@enderror</p>

                                {{-- スポンサー名 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">登録スポンサー名</label>
                                <input type="text" name="title"
                                        value="{{ old('title') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                <p data-error-for="title" class="text-red-500 text-xs mt-1 empty:hidden">@error('title'){{ $message }}@enderror</p>

                                {{-- 表示秒数 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">表示秒数<span class="text-gray-500 text-xs">（画像のみ。動画は最後まで再生します）</span></label>
                                <input type="number" name="display_seconds"
                                        value="{{ old('display_seconds', 5) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                <p data-error-for="display_seconds" class="text-red-500 text-xs mt-1 empty:hidden">@error('display_seconds'){{ $message }}@enderror</p>

                                {{-- 開始日 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                                <input type="date" name="start_date"
                                        value="{{ old('start_date') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                <p data-error-for="start_date" class="text-red-500 text-xs mt-1 empty:hidden">@error('start_date'){{ $message }}@enderror</p>

                                {{-- 終了日 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                                <input type="date" name="end_date"
                                        value="{{ old('end_date') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                <p data-error-for="end_date" class="text-red-500 text-xs mt-1 empty:hidden">@error('end_date'){{ $message }}@enderror</p>

                                {{-- 公開中かどうか --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">公開</label>
                                <input type="checkbox" name="is_active" value="1"
                                        @checked(old('is_active'))
                                        class="rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                <p data-error-for="is_active" class="text-red-500 text-xs mt-1 empty:hidden">@error('is_active'){{ $message }}@enderror</p>

                                <div class="mt-6 flex justify-center">
                                    <button type="submit"
                                            class="px-6 py-2 bg-slate-700 text-white text-lg rounded-md hover:bg-slate-600 transition
                                                   disabled:opacity-50 disabled:cursor-wait">
                                            登録
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/sponsor-form.js')
    @endpush
</x-app-layout>
