<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録完了 | HAZY BOULDER</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/guest-complete.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white/80 rounded-lg shadow p-8 text-center">
            <h1 class="text-lg font-semibold text-gray-800 mb-3">
                登録ありがとうございました
            </h1>
            <p class="text-sm text-gray-600 leading-relaxed">
                会員登録が完了しました。<br>
                受付スタッフにお声がけください。
            </p>
            
            {{-- 会員番号があるときだけ表示 --}}
            @if (!empty($code))
                <div class="mt-6">
                    <p class="text-2xl font-bold text-gray-800 mb-1">会員番号</p>
                    <p class="text-3xl font-mono tracking-widest mb-4">{{ $code }}</p>

                    {{-- QRを描く場所。data-code に会員番号を渡す --}}
                    <canvas id="qr-code" data-code="{{ $code }}" class="mx-auto"></canvas>

                    <p class="text-xs text-gray-500 mt-3">
                        この画面をスクリーンショットで保存してください
                    </p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>