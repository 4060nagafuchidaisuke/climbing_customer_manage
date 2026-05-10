<!-- welcome.blade.php -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HAZY BOULDER - 受付</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        .bg-image {
            /* 画像のパスを指定 */
            background-image: url("{{ asset('images/HAZY_bolder_Reception.jpg') }}");

            /* 背景を画面いっぱいに固定する設定 */
            height: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;

            /* その上のコンテンツを中央に配置するためのFlexbox */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .content-box {
            background: rgba(255, 255, 255, 0.8);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <div class="bg-image">
        <!-- ここに透過ロゴを配置 -->
        <img src="{{ asset('images/HAZY_Bolder_logo.png') }}" alt="Logo" style="width: 200px; margin-bottom: 20px;">

        <div class="content-box">
            <h1>WELCOME TO HAZY BOULDER</h1>
            <p>こちらでチェックインをお願いします</p>
            <!-- ここにLaravelの認証ボタンやフォームを配置 -->
            <a href="{{ route('login') }}" class="btn">ログイン / チェックイン</a>
        </div>
    </div>

</body>
</html>