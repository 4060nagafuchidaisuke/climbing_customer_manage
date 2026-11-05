
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

// ── 時計 ──
function updateClock() {
    const el = document.getElementById('clock');
    if (el) el.textContent = new Date().toLocaleString('ja-JP', {
        year: 'numeric', month: 'long', day: 'numeric',
        weekday: 'short', hour: '2-digit', minute: '2-digit'
    }) + ' 現在';
}
updateClock();
setInterval(updateClock, 1000);

// ── 自動フォーカス＆結果フェードアウト ──
document.getElementById('barcode')?.focus();
if (document.getElementById('checkin-root')?.dataset.hasResult) {
    setTimeout(() => {
        const input = document.getElementById('barcode');
        if (input) { input.value = ''; input.focus(); }
        const resultBox = document.getElementById('result-box');
        if (resultBox) {
            resultBox.style.opacity = '0';
            setTimeout(() => { resultBox.style.display = 'none'; }, 600);
        }
    }, 3000);
}

// ── カメラ ──
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
        fps: 10, qrbox: { width: 250, height: 250 },
        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
    }, (decoded) => {
        document.getElementById('barcode').value = decoded;
        stopCamera().then(() => document.getElementById('checkin-form').submit());
    }, () => {})
    .catch((err) => {
        console.error(err);
        alert(
            "カメラ起動失敗\n\n" +
            "name : " + err.name + "\n" +
            "message : " + err.message
        );

        stopCamera();
    });
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
document.getElementById('camera-btn')?.addEventListener('click', toggleCamera);

// ── 広告カルーセル（フェード切り替え）──
const track = document.getElementById('ad-track');
const slides = track ? Array.from(track.children) : [];

if (slides.length > 0) {
    const DEFAULT_SECONDS = 5; // data-seconds が空・不正なときの表示秒数
    const rotates = slides.length > 1; // 1枚だけなら切り替えない（動画はループ再生のまま）
    let current = 0;
    let timer = null;

    // 画像の表示秒数。値が欠けていてもカルーセルが止まらないよう既定値に逃がす
    function secondsOf(slide) {
        const seconds = Number(slide.dataset.seconds);
        return Number.isFinite(seconds) && seconds > 0 ? seconds : DEFAULT_SECONDS;
    }

    // 次のスライドへ（最後の次は最初に戻る）
    function next() {
        current = (current + 1) % slides.length;
        showSlide(current);
    }

    // index 番目のスライドだけを表示し、次へ進む合図を仕掛ける
    //   画像：data-seconds 秒後に次へ
    //   動画：頭から再生し、最後まで再生したら次へ
    function showSlide(index) {
        clearTimeout(timer);

        slides.forEach((slide, i) => {
            const isActive = i === index;
            slide.classList.toggle('opacity-0', !isActive); // 表示中のスライドだけ透明を外す

            const video = slide.querySelector('video');
            if (!video) return;
            video.onended = null; // 前の周回で付けた「終わったら次へ」を外す
            if (isActive) {
                video.currentTime = 0;
                video.play().catch(() => {
                    // 再生できない（ファイル破損など）ときは、画像と同じく秒数で次へ
                    if (rotates) timer = setTimeout(next, secondsOf(slide) * 1000);
                });
            } else {
                video.pause();
            }
        });

        if (!rotates) return;

        const video = slides[index].querySelector('video');
        if (video) {
            video.onended = next;
        } else {
            timer = setTimeout(next, secondsOf(slides[index]) * 1000);
        }
    }

    // 複数枚のときは loop を外す（loop 中は ended が発生せず、次へ進めないため）
    if (rotates) {
        slides.forEach((slide) => {
            const video = slide.querySelector('video');
            if (video) video.loop = false;
        });
    }

    showSlide(0);
}