<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRController extends Controller
{
    public function show()
    {
        // 15分間だけ有効な、署名入りのフォームURLを生成
        $url = URL::temporarySignedRoute('register.guest.form', now()->addMinutes(15));

        // URLをQRコード画像（SVG）に変換
        $qrCode = QrCode::size(300)->generate($url);

        return view('members.register_qr', ['qrCode' => $qrCode, 'url' => $url]);

    }
}
