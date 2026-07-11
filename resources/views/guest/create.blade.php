<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録 | HAZY BOULDER</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- 見出し --}}
            <h1 class="font-semibold text-xl text-gray-800 mb-6">会員登録</h1>

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

            <form method="POST" action="{{ route('register.guest.confirm') }}">
                @csrf
                <input type="hidden" name="signed_url" value="{{ url()->full() }}">

                <div class="space-y-6">

                    {{-- 基本情報 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">基本情報</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    姓 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="last_name"
                                       value="{{ old('last_name', session('guest_member.last_name')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('last_name') border-red-400 @enderror">
                                @error('last_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    名 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="first_name"
                                       value="{{ old('first_name', session('guest_member.first_name')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('first_name') border-red-400 @enderror">
                                @error('first_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    姓（カナ）<span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="last_name_kana"
                                       value="{{ old('last_name_kana', session('guest_member.last_name_kana')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('last_name_kana') border-red-400 @enderror">
                                @error('last_name_kana')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    名（カナ）<span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="first_name_kana"
                                       value="{{ old('first_name_kana', session('guest_member.first_name_kana')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('first_name_kana') border-red-400 @enderror">
                                @error('first_name_kana')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    生年月日 <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="birth_date"
                                       value="{{ old('birth_date', session('guest_member.birth_date')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('birth_date') border-red-400 @enderror">
                                @error('birth_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">性別</label>
                                <select name="gender"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                               focus:ring-slate-500 focus:border-slate-500">
                                    <option value="">選択してください</option>
                                    <option value="male"   {{ old('gender', session('guest_member.gender')) === 'male' ? 'selected' : '' }}>男性</option>
                                    <option value="female" {{ old('gender', session('guest_member.gender')) === 'female' ? 'selected' : '' }}>女性</option>
                                    <option value="other"  {{ old('gender', session('guest_member.gender')) === 'other' ? 'selected' : '' }}>その他</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    電話番号 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="phone" inputmode="numeric"
                                       value="{{ old('phone', session('guest_member.phone')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('phone') border-red-400 @enderror">
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス</label>
                                <input type="email" name="email"
                                       value="{{ old('email', session('guest_member.email')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('email') border-red-400 @enderror">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">郵便番号</label>
                                <input type="text" name="postal_code" inputmode="numeric"
                                       value="{{ old('postal_code', session('guest_member.postal_code')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('postal_code') border-red-400 @enderror">
                                @error('postal_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">クライミングレベル</label>
                                <select name="climbing_level"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                               focus:ring-slate-500 focus:border-slate-500">
                                    <option value="">選択してください</option>
                                    <option value="beginner" {{ old('climbing_level', session('guest_member.climbing_level')) === 'beginner' ? 'selected' : '' }}>初心者</option>
                                    <option value="intermediate" {{ old('climbing_level', session('guest_member.climbing_level')) === 'intermediate' ? 'selected' : '' }}>中級者</option>
                                    <option value="advanced" {{ old('climbing_level', session('guest_member.climbing_level')) === 'advanced' ? 'selected' : '' }}>上級者</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    住所 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="address"
                                       value="{{ old('address', session('guest_member.address')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('address') border-red-400 @enderror">
                                @error('address')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">職業</label>
                                <input type="text" name="occupation"
                                       value="{{ old('occupation', session('guest_member.occupation')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>
                        </div>
                    </div>

                    {{-- 緊急連絡先 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">緊急連絡先</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    氏名 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="emergency_name"
                                       value="{{ old('emergency_name', session('guest_member.emergency_name')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('emergency_name') border-red-400 @enderror">
                                @error('emergency_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">続柄</label>
                                <input type="text" name="emergency_relation"
                                       value="{{ old('emergency_relation', session('guest_member.emergency_relation')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    電話番号 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="emergency_phone" inputmode="numeric"
                                       value="{{ old('emergency_phone', session('guest_member.emergency_phone')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('emergency_phone') border-red-400 @enderror">
                                @error('emergency_phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 未成年・保護者情報 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">未成年・保護者情報</h3>
                        <div class="mb-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" name="is_minor" value="1"
                                       {{ old('is_minor', session('guest_member.is_minor')) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-slate-600
                                              focus:ring-slate-500">
                                未成年である
                            </label>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">保護者氏名</label>
                                <input type="text" name="guardian_name"
                                       value="{{ old('guardian_name', session('guest_member.guardian_name')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">保護者電話番号</label>
                                <input type="text" name="guardian_phone" inputmode="numeric"
                                       value="{{ old('guardian_phone', session('guest_member.guardian_phone')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('guardian_phone') border-red-400 @enderror">
                                @error('guardian_phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 使用同意書 --}}
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">使用同意書</h3>

                        {{-- スクロール枠 --}}
                        <div class="max-h-64 overflow-y-auto border rounded p-4 bg-white/50 text-sm text-gray-600">
                            {{-- 同意書の本文をここに --}}
                        </div>

                        {{-- チェックボックス --}}
                        <label class="flex items-center gap-2 mt-3">
                            <input type="checkbox" name="agreement" value="1"
                                   {{ old('agreement') ? 'checked' : '' }}>
                            <span>上記の内容に同意します</span>
                        </label>

                        @error('agreement')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ボタン --}}
                    <div class="flex justify-end items-center">
                        <button type="submit"
                                class="px-6 py-2 bg-slate-700 text-white text-sm rounded-md
                                       hover:bg-slate-600 transition font-medium">
                            確認する
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</body>
</html>