import QRCode from 'qrcode';

// data 属性から会員番号を受け取る要素を探す
const el = document.getElementById('qr-code');

if (el) {
    const code = el.dataset.code;  // blade から渡された会員番号

    // canvas にQRを描く
    QRCode.toCanvas(el, code, { width: 200 }, function (error) {
        if (error) console.error(error);
    });
}