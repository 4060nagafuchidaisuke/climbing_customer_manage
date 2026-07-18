<x-app-layout
background="images/Profile_Details_.webp"
    bgPosition="center bottom"
    bgSize="70%"
>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">料金の編集</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow p-6">

                {{-- 変更不可の情報（正体なので表示だけ） --}}
                <p class="text-sm text-gray-500 mb-4">
                    {{ $plan->price_tier->label() }} / {{ $plan->plan_type->label() }}
                </p>

                <form method="POST" action="{{ route('plans.update', $plan) }}">
                    @csrf
                    @method('PUT')

                    {{-- 料金 --}}
                    <label class="block text-sm font-medium">料金</label>
                    <input type="number" name="price"
                           value="{{ old('price', $plan->price) }}"
                           class="mt-1 w-full border rounded px-3 py-2">
                    @error('price') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

                    {{-- 有効フラグ：hidden とチェックボックスのセット --}}
                    <div class="mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <label>
                            <input type="checkbox" name="is_active" value="1"
                                   @checked(old('is_active', $plan->is_active))>
                            有効にする
                        </label>
                    </div>

                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('plans.index') }}" class="text-gray-500">← 戻る</a>
                        <button type="submit"
                                class="bg-slate-800 text-white px-4 py-2 rounded">保存</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>