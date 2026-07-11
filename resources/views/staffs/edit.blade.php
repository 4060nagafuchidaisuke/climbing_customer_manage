<x-app-layout
    background="images/SystemSetting.webp"
    bgPosition="center bottom"
    bgSize="50%"
>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staffs.index') }}"
               class="text-gray-400 hover:text-gray-600 text-sm">← 一覧に戻る</a>
            <h2 class="font-semibold text-xl text-gray-800">スタッフ編集 — {{ $staff->name }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4">
            <form method="POST" action="{{ route('staffs.update', $staff) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">氏名</label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">電話番号</label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">住所</label>
                    <input type="text" name="address" value="{{ old('address', $staff->address) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">パスワード(8文字以上)</label>
                    <input type="password" name="password"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                           placeholder="変更する場合のみ入力">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">パスワード（確認）</label>
                    <input type="password" name="password_confirmation"
                           class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">権限</label>
                    <select name="role" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach(\App\Enums\StaffRole::cases() as $role)
                            <option value="{{ $role->value }}"
                                @selected(old('role', $staff->role->value) === $role->value)>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           @checked(old('is_active', $staff->is_active))>
                    <label for="is_active" class="text-base font-semibold text-gray-800">有効（ログイン可能）</label>
                </div>

                <div class="mt-6 flex justify-center">
                    <button type="submit"
                            class="px-6 py-2 bg-slate-700 text-white text-lg rounded-md hover:bg-slate-600 transition">
                        更新
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
