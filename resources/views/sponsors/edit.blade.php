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
                    スポンサー広告の編集
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

            {{-- 編集画面 --}}
            <div class="bg-white/80 rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">★「{{ $sponsor->title }}」の編集</h3>
                <p class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-md text-amber-800 text-sm">
                    再生順は、一覧画面の ↑↓ ボタンで変えられます。受付画面で再生されるのは、公開中かつ掲載期間内のもののうち、<strong>上位{{ \App\Models\Sponsor::CHECKIN_LIMIT }}つまで</strong>です。
                </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            {{-- id / data-max-mb は sponsor-form.js が使う。ページを読み込み直さずに送るので、エラーでもファイル選択が消えない --}}
                            {{-- ファイルを送るため POST で送り、@method('PUT') で update として受け取らせる --}}
                            <form id="sponsor-form" method="POST" action="{{ route('sponsors.update', $sponsor) }}" enctype="multipart/form-data"
                                  data-max-mb="50">
                            @csrf
                            @method('PUT')
                                @include('sponsors._form', ['sponsor' => $sponsor])

                                <div class="mt-6 flex justify-center">
                                    <button type="submit"
                                            class="px-6 py-2 bg-slate-700 text-white text-lg rounded-md hover:bg-slate-600 transition
                                                   disabled:opacity-50 disabled:cursor-wait">
                                            更新
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
