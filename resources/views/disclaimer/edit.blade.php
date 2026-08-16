<x-app-layout
    background="images/Profile_Details_.webp"
    bgPosition="center bottom"
    bgSize="100%"
>

    <x-slot name="header">
        <div class="grid grid-cols-[auto_1fr] md:grid-cols-3 items-center gap-3 md:gap-0">

            {{-- 左側：戻るボタン --}}
            <div class="text-left">
                <a href="{{ route('disclaimer.show') }}"
                    class="text-gray-400 hover:text-gray-600 text-sm whitespace-nowrap">← 戻る</a>
            </div>

            {{-- 中央：見出し（スマホでは残りのスペースで中央、PCでは画面全体の中央） --}}
            <div class="text-center md:col-start-2">
                <h2 class="font-semibold text-2xl text-gray-800">免責事項の編集</h2>
            </div>

            {{-- 右側：PC用バランサー（スマホでは非表示） --}}
            <div class="hidden md:block">
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4">

        {{-- お知らせ入力欄 --}}
            {{-- title フィールド --}}
            <form method="POST" action="{{ route('disclaimer.update', $disclaimer) }}" class="mb-6">
                @csrf
                @method('PUT')
                {{-- body の入力欄 --}}
                <div class="mb-4">
                    <label class="block text-xl font-semibold text-white mb-2">免責事項の内容</label>

                        {{-- 失敗時の復元のみ --}}
                        <textarea name="content" rows="10" class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('body', $disclaimer->content) }}</textarea>

                    @error('content')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 登録ボタン --}}
                <div class="mt-6 flex justify-center">
                    <button type="submit"
                        class="px-4 py-2 bg-slate-700 text-white text-xl rounded-md hover:bg-slate-600 transition">
                        登録
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>