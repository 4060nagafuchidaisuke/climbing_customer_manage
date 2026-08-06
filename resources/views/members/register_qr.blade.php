<x-app-layout
    background="images/NewMember.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
   <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← 一覧に戻る</a>
            <h2 class="font-semibold text-xl text-gray-800">新規登録用QRコード</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-md mx-auto px-4 text-center">
            <div class="bg-white rounded-lg shadow p-8">

                <p class="text-gray-600 text-sm mb-6">
                    登録画面へ進みます。お客様のスマートフォンで、このQRコードを読み取ってください。<br>
                </p>

                {{-- ここにQRコードを表示 --}}
                <div class="flex justify-center mb-6">
                    {!! $qrCode !!}
                </div>

                {{-- 開発用：PCから確認するためのテストリンク（本番前に消す） --}}
                <a href="{{ $url }}" class="text-blue-500 text-sm underline break-all">
                    {{ $url }}
                </a>

            </div>
        </div>
    </div>
</x-app-layout>