<x-app-layout
    background="images/Profile_Details_.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
    <x-slot name="header">
        <div class="grid grid-cols-[auto_1fr] md:grid-cols-3 items-center gap-3 md:gap-0">

            {{-- 左側：戻るボタン --}}
            <div class="text-left">
                <a href="{{ route('notices.index') }}"
                    class="text-gray-400 hover:text-gray-600 text-sm whitespace-nowrap">← 一覧に戻る</a>
            </div>

            {{-- 中央：見出し（スマホでは残りのスペースで中央、PCでは画面全体の中央） --}}
            <div class="text-center md:col-start-2">
                <h2 class="font-semibold text-2xl text-gray-800">お知らせ入力</h2>
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
            <form method="POST" action="{{ route('notices.store') }}" class="mb-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-xl font-semibold text-white mb-2">お知らせのタイトル</label>
                    <input  type="text"
                            name="title"
                            value="{{ old('title') }}"
                            placeholder="例：忘れ物"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- body の入力欄 --}}
                <div class="mb-4">
                    <label class="block text-xl font-semibold text-white mb-2">内容</label>
                    <textarea name="body" rows="10" class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 掲載終了予定 --}}
                <label class="block text-base font-semibold text-white mb-2">
                    掲載終了日
                </label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}">

                {{-- 登録ボタン --}}
                <div class="mt-6 flex justify-center">
                    <button type="submit"
                        class="px-4 py-2 bg-slate-700 text-white text-xl rounded-md hover:bg-slate-600 transition">
                        登録
                    </button>
                </div>

                {{-- バリデーションエラー --}}
                @error('body')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>
</x-app-layout>