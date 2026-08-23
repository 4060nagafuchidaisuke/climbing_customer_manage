@push('scripts')
    @vite('resources/js/guest-complete.js')
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('members.show', $member) }}"
                   class="text-gray-400 hover:text-gray-600 text-sm">← 詳細に戻る</a>
                <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                    会員情報の編集
                </h2>
                <span class="text-sm text-gray-500 font-mono">{{ $member->member_code }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

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

            {{-- 会員証（QRコード再発行用） --}}
            <div class="mb-6 bg-white/80 rounded-lg shadow p-6 flex flex-col items-center">
                <h3 class="font-semibold text-gray-700 mb-3">会員証（QRコード）</h3>
                <canvas id="qr-code" data-code="{{ $member->member_code }}"></canvas>
                <p class="mt-3 text-2xl font-mono tracking-widest text-gray-800">{{ $member->member_code }}</p>
                <p class="mt-1 text-xs text-gray-400">
                    お客様がスクショを紛失した場合は、この画面を提示して再撮影してもらってください
                </p>
            </div>

            <form method="POST" action="{{ route('members.update', $member) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">

                    {{-- 基本情報 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">基本情報</h3>
                        <div class="grid grid-cols-2 gap-4">

                            {{-- 会員番号の編集--}}
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">会員番号<span class="text-red-500">*</span></label>
                                <input type="text" name="member_code"
                                        value="{{ old('member_code', $member->member_code) }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                            focus:ring-slate-500 focus:border-slate-500">
                                @error('member_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- 名前の管理--}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    姓 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="last_name"
                                       value="{{ old('last_name', $member->last_name) }}"
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
                                       value="{{ old('first_name', $member->first_name) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('first_name') border-red-400 @enderror">
                                @error('first_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    姓（全角カナ）<span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="last_name_kana"
                                       value="{{ old('last_name_kana', $member->last_name_kana) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('last_name_kana') border-red-400 @enderror">
                                @error('last_name_kana')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    名（全角カナ）<span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="first_name_kana"
                                       value="{{ old('first_name_kana', $member->first_name_kana) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('first_name_kana') border-red-400 @enderror">
                                @error('first_name_kana')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- 生年月日--}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    生年月日 <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="birth_date"
                                       value="{{ old('birth_date', $member->birth_date?->format('Y-m-d')) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500
                                              @error('birth_date') border-red-400 @enderror">
                                @error('birth_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- 年齢カテゴリ --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">会員区分（一般・シニア・学生）<span class="text-red-500">*</span></label>
                                <select name="category"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-slate-500 focus:border-slate-500">
                                    <option value="">選択してください</option>
                                    @foreach (\App\Enums\MemberCategory::cases() as $case)
                                        <option value="{{ $case->value }}"
                                            @selected(old('category', $member->category?->value) === $case->value)>
                                            {{ $case->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">性別</label>
                                <select name="gender"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                               focus:ring-slate-500 focus:border-slate-500">
                                    <option value="">選択してください</option>
                                    @foreach(\App\Enums\Gender::cases() as $case)
                                        <option value="{{ $case->value }}"
                                            @selected(old('gender', $member->gender?->value) === $case->value)>
                                            {{ $case->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">電話番号（半角数字）</label>
                                <input type="text" name="phone"
                                       value="{{ old('phone', $member->phone) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス（半角英数字）</label>
                                <input type="email" name="email"
                                       value="{{ old('email', $member->email) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">郵便番号（半角数字）</label>
                                <input type="text" name="postal_code"
                                       value="{{ old('postal_code', $member->postal_code) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">クライミングレベル</label>
                                <select name="climbing_level"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                               focus:ring-slate-500 focus:border-slate-500">
                                    <option value="">選択してください</option>
                                    @foreach(\App\Enums\ClimbingLevel::cases() as $case)
                                        <option value="{{ $case->value }}"
                                            @selected(old('climbing_level', $member->climbing_level?->value) === $case->value)>
                                            {{ $case->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">住所</label>
                                <input type="text" name="address"
                                       value="{{ old('address', $member->address) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">負傷・注意事項</label>
                                <textarea name="injury_notes" rows="2"
                                          class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                                 focus:ring-slate-500 focus:border-slate-500">{{ old('injury_notes', $member->injury_notes) }}</textarea>
                            </div>

                        </div>
                    </div>

                    {{-- 緊急連絡先 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">緊急連絡先</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">氏名</label>
                                <input type="text" name="emergency_name"
                                       value="{{ old('emergency_name', $member->emergency_name) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">続柄</label>
                                <input type="text" name="emergency_relation"
                                       value="{{ old('emergency_relation', $member->emergency_relation) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">電話番号（半角数字）</label>
                                <input type="text" name="emergency_phone"
                                       value="{{ old('emergency_phone', $member->emergency_phone) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>
                        </div>
                    </div>

                    {{-- 未成年・保護者情報 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">未成年・保護者情報</h3>
                        <div class="mb-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" name="is_minor" value="1"
                                       {{ old('is_minor', $member->is_minor) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-slate-600
                                              focus:ring-slate-500">
                                未成年である
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">保護者氏名</label>
                                <input type="text" name="guardian_name"
                                       value="{{ old('guardian_name', $member->guardian_name) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">保護者電話番号（半角数字）</label>
                                <input type="text" name="guardian_phone"
                                       value="{{ old('guardian_phone', $member->guardian_phone) }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                              focus:ring-slate-500 focus:border-slate-500">
                            </div>
                        </div>
                    </div>

                    {{-- スタッフメモ --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">スタッフ用メモ</h3>
                        <div class="mb-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" name="caution_flag" value="1"
                                       {{ old('caution_flag', $member->caution_flag) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-red-500
                                              focus:ring-red-400">
                                <span class="text-red-600 font-medium">注意フラグを立てる</span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">注意メモ</label>
                            <textarea name="caution_notes" rows="2"
                                      class="w-full rounded-md border-gray-300 shadow-sm text-sm
                                             focus:ring-slate-500 focus:border-slate-500">{{ old('caution_notes', $member->caution_notes) }}</textarea>
                        </div>
                    </div>

                    {{-- プラン変更 --}}
                    <div class="mt-6 bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">プラン変更</h3>

                        {{-- 現在のプラン --}}
                        <p class="text-sm text-gray-600 mb-4">
                            現在の利用タイプ：
                            <span class="font-medium">
                                {{ $member->activePlan?->plan->plan_type->label() ?? '（未設定）' }}
                            </span>
                        </p>
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    利用タイプ <span class="text-red-500">*</span>
                                </label>
                                <select name="plan_type"
                                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-slate-500 focus:border-slate-500">
                                    <option value="">選択してください</option>
                                    @foreach (\App\Enums\PlanType::cases() as $case)
                                        <option value="{{ $case->value }}"
                                            @selected(old('plan_type', $member->activePlan?->plan->plan_type?->value) == $case->value)>
                                            {{ $case->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ボタン --}}
                    <div class="flex justify-between items-center">
                        <a href="{{ route('members.show', $member) }}"
                           class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md
                                  hover:bg-gray-300 transition">
                            キャンセル
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-slate-700 text-white text-sm rounded-md
                                       hover:bg-slate-600 transition font-medium">
                            更新する
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>