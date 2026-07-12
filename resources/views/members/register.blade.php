<x-app-layout
    
    background="images/NewMember.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
@if (session('success'))
    <div class="mb-4 rounded-md bg-green-100 border border-green-300 px-4 py-3 text-green-800">
        {{ session('success') }}
    </div>
@endif
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('members.index') }}"
                   class="text-gray-400 hover:text-gray-600 text-sm">← 一覧に戻る</a>
                <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                    会員タイプ変更
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 bg-white p-6 rounded-lg shadow">
            <p class="text-sm text-gray-500">対象の会員</p>
            <p class="text-lg font-semibold text-gray-800">{{ $member->last_name }} {{ $member->first_name }}</p>
        </div>

            <form method="POST" action="{{ route('members.register.store', $member) }}">
                @csrf
                
                {{-- カテゴリーの選択 --}}
                <select name="category">
                    <option value="">選択してください</option>
                    @foreach (\App\Enums\MemberCategory::cases() as $case)
                        <option value="{{ $case->value }}" @selected(old('category') == $case->value)>{{ $case->label() }}</option>
                    @endforeach
                </select>

                {{-- チェックボックス --}}
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="payment_received" value="1">
                    <span>¥1,000 を受領しました</span>
                </label>

                {{-- バリテーション --}}
                @error('category')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror

                @error('payment_received')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
                
                {{-- ボタン --}}
                <div class="flex justify-between items-center">
                    <a href="{{ route('members.index') }}"
                       class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 transition">
                            キャンセル
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition font-medium">
                        登録する
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>