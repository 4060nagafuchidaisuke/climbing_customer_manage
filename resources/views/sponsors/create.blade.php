<x-app-layout
    background="images/NewMember.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('sponsors.index') }}"
                   class="text-gray-400 hover:text-gray-600 text-sm">← 一覧に戻る</a>
                <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                    スポンサー登録（動画・静止画）
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- バリデーションエラー表示 --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 font-medium text-sm mb-2">入力内容を確認してください：</p>
                    <ul class="text-red-600 text-sm list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 登録画面 --}}
            <div class="bg-white/80 rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">★スポンサー名の入力（<span class="text-red-500">*</span>は必須入力項目です）</h3>
                    <div class="grid grid-cols-2 gap-4">
                        {{-- スポンサー登録 --}}
                        <div class="col-span-2">
                            {{-- スポンサー名 --}}
                            <form method="POST" action="{{ route('sponsors.store') }}" enctype="multipart/form-data">
                            @csrf
                                {{-- ファイルの選択 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">画像・動画</label>
                                <input type="file" name="media" accept="image/*,video/mp4">
                                    @error('media')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror

                                {{-- スポンサー名 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">登録スポンサー名</label>
                                <input type="text" name="title"
                                        value="{{ old('title') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                    @error('title')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror

                                {{-- 表示秒数 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">表示秒数</label>
                                <input type="number" name="display_seconds"
                                        value="{{ old('display_seconds') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                    @error('display_seconds')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror         

                                {{-- 表示順 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">表示順</label>
                                <input type="number" name="sort_order"
                                        value="{{ old('sort_order') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                    @error('sort_order')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror    
                                
                                {{-- 開始日 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
                                <input type="date" name="start_date"
                                        value="{{ old('start_date') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                    @error('start_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror

                                {{-- 終了日 --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
                                <input type="date" name="end_date"
                                        value="{{ old('end_date') }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                    @error('end_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror

                                {{-- 公開中かどうか --}}
                                <label class="block text-sm font-medium text-gray-700 mb-1">公開</label>
                                <input type="checkbox" name="is_active" value="1"
                                        class="rounded-md border-gray-300 shadow-sm text-sm
                                                focus:ring-slate-500 focus:border-slate-500">
                                    @error('is_active')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                
                                <div class="mt-6 flex justify-center">
                                    <button type="submit"
                                            class="px-6 py-2 bg-slate-700 text-white text-lg rounded-md hover:bg-slate-600 transition">
                                            登録
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>