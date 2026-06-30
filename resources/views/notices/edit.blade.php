<x-app-layout
    background="images/Profile(Details).png"
    bgPosition="center bottom"
    bgSize="100%"
>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('notices.index') }}"
                   class="text-gray-400 hover:text-gray-600 text-sm">← 一覧に戻る</a>
                <h2 class="font-semibold text-xl text-gray-800">
                    お知らせ入力編集画面
                </h2>
            </div>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4">

        {{-- お知らせ入力欄 --}}
            {{-- title フィールド --}}
            <form method="POST" action="{{ route('notices.update', $notice) }}" class="mb-6">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-xl font-semibold text-white mb-2">
                        お知らせのタイトル
                    </label>

                        {{-- 失敗時の復元のみ --}}
                        <input type="text" name="title" value="{{ old('title', $notice->title) }}" placeholder="例：忘れ物" class="w-full rounded-md border-gray-300 shadow-sm text-sm">

                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- body の入力欄 --}}
                <div class="mb-4">
                    <label class="block text-xl font-semibold text-white mb-2">内容</label>

                        {{-- 失敗時の復元のみ --}}
                        <textarea name="body" rows="10" class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('body', $notice->body) }}</textarea>

                    @error('body')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 掲載終了予定 --}}
                <label class="block text-base font-semibold text-white mb-2">
                    掲載終了日
                </label>
                <input type="date" name="expires_at" value="{{ old('expires_at', $notice->expires_at?->format('Y-m-d')) }}">

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