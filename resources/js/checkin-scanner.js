
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

// ── 広告カルーセル（無限ループ）──
const track = document.getElementById('ad-track');
const adCount = Number(track?.dataset.sponsorCount ?? 0);

// 広告が2枚以上あるときだけ動かす
if (track && adCount > 1) {
    // ① 1枚目の複製を末尾に足す： [1][2][3] → [1][2][3][1']
    track.appendChild(track.firstElementChild.cloneNode(true));

    let adIndex = 0;

    // ② 5秒ごとに1枚ぶん左へ進める
    setInterval(() => {
        adIndex++;
        track.style.transition = '';  // アニメを有効に戻す（④で切っているため）
        track.style.transform = `translateX(-${adIndex * 100}%)`;
    }, 5000);

    // ③ スライドのアニメが終わった瞬間に呼ばれる
    track.addEventListener('transitionend', (e) => {
        // 自分自身の transform 以外は無視する
        if (e.target !== track || e.propertyName !== 'transform') return;

        // まだクローンに到達していなければ何もしない
        if (adIndex < adCount) return;

        // ④ クローン(1')に着いた ＝ 見た目は1枚目。本物の1枚目へ瞬間移動する
        adIndex = 0;
        track.style.transition = 'none';          // アニメを一時的に切る
        track.style.transform = 'translateX(0)';  // 瞬間移動
        void track.offsetHeight;                  // 強制リフロー（後述）
    });
}