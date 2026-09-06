<x-app-layout 
    background="images/Reception.webp"
    bgPosition="center bottom"
    bgSize="55%"
>
    <x-slot name="header">
        <div class="relative flex items-center">
            <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                来店・退店受付
            </h2>
            <span class="ml-auto text-sm text-gray-500">
                {{ now()->isoFormat('YYYY年M月D日（ddd）HH:mm') }} 現在
            </span>
        </div>
    </x-slot>
{{-- <div style="position:fixed;top:0;left:0;background:red;color:#fff;z-index:9999;font-size:24px;padding:4px">
    幅: <span id="__w"></span>px
</div> --}}
<script>document.getElementById('__w').textContent = window.innerWidth;</script>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div id="checkin-root" data-has-result="{{ session('result_status') || session('checkin_error') ? '1' : '' }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- ── 左カラム：来退店 ＋ お知らせ ── --}}
                <div class="space-y-5">

                    {{-- 来退店エリア（今の中身をそのまま） --}}
                    <div class="bg-white/90 backdrop-blur rounded-2xl shadow p-6 space-y-4">
                        <p class="text-sm font-semibold text-gray-500">
                            バーコードをスキャン、または会員番号を入力してください
                        </p>
                        <form method="POST" action="{{ route('checkin.process') }}" id="checkin-form">
                            @csrf
                            <div class="flex gap-3">
                                <input type="text" name="barcode" id="barcode"
                                    class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-lg font-mono tracking-widest focus:ring-2 focus:ring-sky-400 outline-none"
                                    placeholder="5桁の会員番号を入力（例:00001）" autocomplete="off" autofocus />
                                <button type="submit"
                                    class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-6 py-3 rounded-xl transition">
                                    受付
                                </button>
                            </div>
                        </form>

                        <button id="camera-btn" 
                            class="w-full flex flex-col items-center justify-center gap-2 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-5 rounded-xl transition">
                            <span class="text-5xl">📷</span>
                            <span class="text-lg">押してカメラを起動させてください</span>
                        </button>

                        <div id="camera-container" class="hidden">
                            <div id="reader" class="rounded-xl overflow-hidden"></div>
                            <p class="text-xs text-center text-gray-400 mt-2">バーコードをカメラに向けてください</p>
                        </div>

                        {{-- 結果表示 --}}
                        <div id="result-box" style="transition: opacity 0.6s ease;">
                            @if (session('result_status') === 'checkin')
                            <div class="bg-emerald-50 border-2 border-emerald-400 rounded-2xl p-8 text-center">
                                <p class="text-5xl mb-3">✅</p>
                                <p class="text-3xl font-black text-emerald-700 mb-2">入店完了</p>
                                <p class="text-2xl font-bold text-gray-800 mb-1">{{ session('result_name') }} さん</p>
                                <p class="text-gray-500 text-lg">{{ session('result_time') }} 入店 · {{ session('result_plan') }}</p>
                            </div>
                            @elseif (session('result_status') === 'checkout')
                            <div class="bg-sky-50 border-2 border-sky-400 rounded-2xl p-8 text-center">
                                <p class="text-5xl mb-3">👋</p>
                                <p class="text-3xl font-black text-sky-700 mb-2">退店完了</p>
                                <p class="text-2xl font-bold text-gray-800 mb-1">{{ session('result_name') }} さん</p>
                                <p class="text-gray-500 text-lg">{{ session('result_time') }} 退店 · 滞在 {{ session('result_duration') }}</p>
                            </div>
                            @elseif (session('checkin_error'))
                            <div class="bg-red-50 border-2 border-red-400 rounded-2xl p-8 text-center">
                                <p class="text-5xl mb-3">❌</p>
                                <p class="text-3xl font-black text-red-700 mb-2">エラー</p>
                                <p class="text-gray-700 text-lg">{{ session('checkin_error') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- お知らせエリア --}}
                    <div class="bg-white/90 backdrop-blur rounded-2xl shadow p-6">
                        <h3 class="font-bold text-gray-700 text-lg mb-4">📢 お知らせ</h3>
                        @if ($notices->isEmpty())
                            <p class="text-gray-400 text-sm">現在お知らせはありません</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($notices as $notice)
                                <li class="flex gap-2 text-gray-700">
                                    <span class="text-emerald-500 font-bold mt-0.5">•</span>
                                    <span>
                                        <span class="font-bold">{{ $notice->title }}</span>
                                        <br>
                                        {{ $notice->body }}
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                </div>

                {{-- ── 右カラム：スポンサー(カルーセル) ── --}}
                <div class="bg-white/90 backdrop-blur rounded-2xl shadow overflow-hidden h-full">
                    @if ($sponsors->isNotEmpty())
                        {{-- 表示窓：overflow-hidden で1枚分だけ見せる --}}
                        <div class="relative h-full bg-gray-900 overflow-hidden">
                            <p class="absolute top-2 right-3 text-xs text-gray-400 z-10">Sponsored</p>

                            {{-- トラック：画像を横並びにした帯。これを動かす --}}
                            <div id="ad-track" class="flex h-full transition-transform duration-700 ease-in-out" data-sponsor-count="{{ $sponsors->count() }}" >
                                @foreach ($sponsors as $sponsor)
                                    {{-- 各画像：窓と同じ幅(w-full)を持つ。flex-shrink-0 で縮まない --}}
                                    <div class="flex-shrink-0 w-full h-full flex items-center justify-center">
                                        <img src="{{ $sponsor->media_url }}" alt="広告"
                                            class="h-full w-full object-contain">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        {{-- 「スポンサー募集中」プレースホルダは今のまま --}}
                        <div class="h-48 flex items-center justify-center border-2 border-dashed border-gray-300">
                            広告主募集中！
                        </div>
                    @endif
                </div>
            </div>
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 bg-slate-50 text-gray-900 text-sm rounded-md hover:bg-slate-600 transition">
                   ＋ ダッシュボードへ
                </a>
        </div>
    </div>
    
     @push('scripts')
        @vite('resources/js/checkin-scanner.js')
    @endpush
</x-app-layout>