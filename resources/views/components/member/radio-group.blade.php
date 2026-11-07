{{--
    会員登録・編集画面の「必須の選択肢」（お店の Forms に合わせた項目）
    使い方：<x-member.radio-group name="residence_area" label="お住まい" :options="[...値 => 表示名]" :value="$現在の値" />
    お客さん用・スタッフ用・編集の3画面で同じ見た目にするためのコンポーネント
--}}
@props(['name', 'label', 'options', 'value' => null])

@php
    // Enum・true/false・文字列のどれが渡されても比較できるように、文字列にそろえる
    // （(string) false は '' になってしまうので、true/false は '1'/'0' にする）
    $current = old($name, $value);
    $current = match (true) {
        $current instanceof \BackedEnum => (string) $current->value,
        is_bool($current) => $current ? '1' : '0',
        default => (string) $current,
    };
@endphp

<div {{ $attributes }}>
    <span class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }} <span class="text-red-500">*</span>
    </span>
    <div class="flex flex-wrap gap-x-6 gap-y-2">
        @foreach($options as $optionValue => $optionLabel)
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="radio" name="{{ $name }}" value="{{ $optionValue }}"
                       @checked($current !== '' && $current === (string) $optionValue)
                       class="border-gray-300 text-slate-600 focus:ring-slate-500">
                {{ $optionLabel }}
            </label>
        @endforeach
    </div>
    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
