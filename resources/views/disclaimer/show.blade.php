<x-app-layout
    background="images/Profile_Details_.webp"
    bgPosition="center bottom"
    bgSize="100%"
>

    <x-slot name="header">
        <div class="grid grid-cols-[auto_1fr] md:grid-cols-3 items-center gap-3 md:gap-0">

            {{-- 左側：戻るボタン --}}
            <div class="text-left">
                <a href="{{ route('dashboard') }}"
                    class="text-gray-400 hover:text-gray-600 text-sm whitespace-nowrap">← ダッシュボードに戻る</a>
            </div>

            {{-- 中央：見出し（スマホでは残りのスペースで中央、PCでは画面全体の中央） --}}
            <div class="text-center md:col-start-2">
                <h2 class="font-semibold text-2xl text-gray-800">現在の免責事項の内容</h2>
            </div>

            {{-- 右側：PC用バランサー（スマホでは非表示） --}}
            <div class="hidden md:block">
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4">
            {{-- 免責事項が無い場合 --}}
            @if($disclaimer)
                <div class="px-4 py-3 text-white  mb-6">
                    {!! nl2br(e($disclaimer->content)) !!}
                </div>

                <div class="flex justify-center">
                    <a href="{{ route('disclaimer.edit') }}"
                       class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                        編集
                    </a>
                </div>
            @else
                <p class="px-4 py-8 text-center text-gray-400">免責事項の入力がありません。</p>
            @endif
        </div>
    </div>
</x-app-layout>