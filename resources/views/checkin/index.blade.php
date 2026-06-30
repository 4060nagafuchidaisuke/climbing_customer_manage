<x-app-layout 
    background="images/Reception.png"
    bgPosition="center bottom"
    bgSize="55%"
>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 tracking-wide">来退店受付</h2>
            <span class="text-sm text-gray-500" id="clock"></span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">

        {{-- ── 上段：来退店 ＋ お知らせ ──────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- 来退店エリア --}}
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow p-6 space-y-4">
                <p class="text-sm font-semibold text-gray-500">
                    バーコードをスキャン、または会員番号を入力してください
                </p>
                <form method="POST" action="{{ route('checkin.process') }}" id="checkin-form">
                    @csrf
                    <div class="flex gap-3">
                        <input type="text" name="barcode" id="barcode"
                            class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-lg font-mono tracking-widest focus:ring-2 focus:ring-sky-400 outline-none"
                            placeholder="M000001" autocomplete="off" autofocus />
                        <button type="submit"
                            class="bg-sky-500 hover:bg-sky-600 text-white font-bold px-6 py-3 rounded-xl transition">
                            受付
                        </button>
                    </div>
                </form>

                <button id="camera-btn" onclick="toggleCamera()"
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

        {{-- ── 下段：広告エリア（横幅いっぱい） ─────────── --}}
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow overflow-hidden">
            @if ($sponsors->isNotEmpty())
                <div class="relative h-48 bg-gray-900 flex items-center justify-center">
                    <p class="absolute top-2 right-3 text-xs text-gray-400">Sponsored</p>
                    <a id="ad-link" href="#" target="_blank" rel="noopener">
                        <img id="ad-image"
                            src="{{ $sponsors->first()->image_url }}"
                            alt="広告"
                            class="h-full max-h-48 w-full object-contain transition-opacity duration-500">
                    </a>
                </div>
            @else
                <div class="h-48 flex items-center justify-center border-2 border-dashed border-gray-300">
                    <div class="text-center text-gray-400">
                        <p class="text-4xl mb-2">📢</p>
                        <p class="font-semibold">広告エリア</p>
                        <p class="text-sm">スポンサー広告を登録してください</p>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        // ── 時計 ────────────────────────────────────────
        function updateClock() {
            const el = document.getElementById('clock');
            if (el) el.textContent = new Date().toLocaleString('ja-JP', {
                year: 'numeric', month: 'long', day: 'numeric',
                weekday: 'short', hour: '2-digit', minute: '2-digit'
            }) + ' 現在';
        }
        updateClock(); setInterval(updateClock, 1000);

        // ── 自動フォーカス ───────────────────────────────
        document.getElementById('barcode')?.focus();
        @if(session('result_status') || session('checkin_error'))
        setTimeout(() => {
            const input = document.getElementById('barcode');
            if (input) { input.value = ''; input.focus(); }

            // 結果表示をフェードアウトさせる
            const resultBox = document.getElementById('result-box');
            if (resultBox) {
                resultBox.style.opacity = '0';
                setTimeout(() => {
                    resultBox.style.display = 'none';
                }, 600);
            }
        }, 3000);
        @endif

        // ── カメラ ───────────────────────────────────────
        let scanner = null, scanning = false;
        async function toggleCamera() {
            scanning ? await stopCamera() : startCamera();
        }
        function startCamera() {
            document.getElementById('camera-container').classList.remove('hidden');
            const btn = document.getElementById('camera-btn');
            btn.innerHTML = '<span class="text-5xl">⏹</span><span class="text-lg">カメラを停止</span>';
            btn.classList.replace('bg-gray-700','bg-red-600');
            btn.classList.replace('hover:bg-gray-600','hover:bg-red-700');
            scanning = true;
            scanner = new Html5Qrcode('reader');
            scanner.start({ facingMode: 'user' }, {
                fps: 10, qrbox: { width: 280, height: 110 },
                formatsToSupport: [Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39, Html5QrcodeSupportedFormats.QR_CODE],
            }, (decoded) => {
                document.getElementById('barcode').value = decoded;
                stopCamera().then(() => document.getElementById('checkin-form').submit());
            }, () => {}).catch(() => { alert('カメラを許可してください'); stopCamera(); });
        }
        async function stopCamera() {
            if (scanner) { await scanner.stop().catch(()=>{}); scanner = null; }
            scanning = false;
            document.getElementById('camera-container').classList.add('hidden');
            const btn = document.getElementById('camera-btn');
            btn.innerHTML = '<span class="text-5xl">📷</span><span class="text-lg">押してカメラを起動させてください</span>';
            btn.classList.replace('bg-red-600','bg-gray-700');
            btn.classList.replace('hover:bg-red-700','hover:bg-gray-600');
        }

        // ── 広告ローテーション ───────────────────────────
        const ads = @json($sponsorData);
        let adIndex = 0;
        function showNextAd() {
            if (ads.length <= 1) return;
            adIndex = (adIndex + 1) % ads.length;
            const img  = document.getElementById('ad-image');
            const link = document.getElementById('ad-link');
            img.style.opacity = '0';
            setTimeout(() => {
                img.src = ads[adIndex].url;
                if (link) link.href = ads[adIndex].link ?? '#';
                img.style.opacity = '1';
                setTimeout(showNextAd, ads[adIndex].seconds * 1000);
            }, 500);
        }
        if (ads.length > 0) {
            setTimeout(showNextAd, ads[0].seconds * 1000);
        }
    </script>
</x-app-layout>