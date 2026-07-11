<!-- welcome.blade.php -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>HAZY BOULDER - 受付</title>
</head>
<body>

    <div class="bg-image">
        <div style="margin-bottom: -20px;">
            <img src="{{ asset('images/HAZY_Bolder_logos.webp') }}" alt="Logo" style="width: 250px;">
        </div>

        <div class="content-box">
            <p class="subtitle">STAFFMEMBERS LOG-IN</p>
            <h1 class="main-title">LOG-IN<br><span class="highlight">HAZY BOULDER</span></h1>
            
            <hr class="divider">
            
            <p class="instruction">お疲れ様です。<br>以下のボタンからログインし会員情報を登録・変更<br>して下さい。</p>
            
            <a href="{{ route('login') }}" class="btn-checkin">ログイン</a>
        </div>
    </div>

</body>
</html>