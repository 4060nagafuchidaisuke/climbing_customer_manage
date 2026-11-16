{{--
    会員登録・編集画面の「プランの選択」
    先に「会員（登録料支払い済み）／ビジター」を選び、その人が選べる利用タイプだけをプルダウンに出す
    使い方：<x-member.plan-select :paid="true/false/null" :plan-type="現在のプランの値 or null" :category="会員区分 or null" />
    料金は「利用タイプ × 会員区分」で決まるので、会員区分もこの中（利用タイプの下）に出す

    プルダウンは会員用とビジター用の2つをサーバーで作っておき、選んだ方だけを表示・送信する
    （見えていない方は disabled にするので送信されない）
--}}
@props(['paid' => null, 'planType' => null, 'category' => null])

@php
    // 会員かビジターかは '1' / '0' / ''（未選択）の文字列にそろえる（Alpine.js とラジオの value に合わせる）
    $currentPaid = old('registration_fee_paid', $paid);
    $currentPaid = match (true) {
        is_bool($currentPaid) => $currentPaid ? '1' : '0',
        default => (string) $currentPaid,
    };
    $currentPlan = (string) old('plan_type', $planType);
    $currentCategory = old('category', $category instanceof \BackedEnum ? $category->value : $category);

    $groups = [
        '1' => \App\Enums\PlanType::optionsFor(true),
        '0' => \App\Enums\PlanType::optionsFor(false),
    ];
@endphp

{{-- 会員／ビジターや利用タイプを最初の状態から変えたら plan-selection-changed イベントで知らせる
     （編集画面の「継続」ボタンは、別のプランを選んでいる間は押せないようにする） --}}
<div x-data="{
        paid: @js($currentPaid),
        plan: @js($currentPlan),
        initialPaid: @js($currentPaid),
        initialPlan: @js($currentPlan),
        notify() {
            $dispatch('plan-selection-changed', { changed: this.paid !== this.initialPaid || this.plan !== this.initialPlan });
        },
    }"
    x-init="
        $watch('paid', () => $nextTick(() => {
            const select = $root.querySelector(`select[data-group='${paid}']`);
            plan = select ? select.value : '';
            notify();
        }));
        $watch('plan', () => notify());
    "
    {{ $attributes }}>
    <span class="block text-sm font-medium text-gray-700 mb-1">
        会員／ビジター <span class="text-xs text-gray-500">※利用タイプを選ぶ場合は必須</span>
    </span>
    <div class="flex flex-wrap gap-x-6 gap-y-2">
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="radio" name="registration_fee_paid" value="1" x-model="paid"
                   @checked($currentPaid === '1')
                   class="border-gray-300 text-slate-600 focus:ring-slate-500">
            会員（登録料支払い済み）
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            <input type="radio" name="registration_fee_paid" value="0" x-model="paid"
                   @checked($currentPaid === '0')
                   class="border-gray-300 text-slate-600 focus:ring-slate-500">
            ビジター
        </label>
    </div>
    @error('registration_fee_paid')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror

    <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">
        利用タイプ <span class="text-xs text-gray-500">※選ぶ場合は会員区分も必須</span>
    </label>

    {{-- 会員用・ビジター用 --}}
    @foreach ($groups as $groupPaid => $options)
        <select name="plan_type" data-group="{{ $groupPaid }}"
                @change="plan = $event.target.value"
                x-show="paid === '{{ $groupPaid }}'"
                :disabled="paid !== '{{ $groupPaid }}'"
                @if ($currentPaid !== (string) $groupPaid) style="display: none" disabled @endif
                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-slate-500 focus:border-slate-500">
            <option value="">選択してください</option>
            @foreach ($options as $value => $label)
                <option value="{{ $value }}" @selected($currentPaid === (string) $groupPaid && $currentPlan === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    @endforeach

    {{-- まだ会員かビジターかを選んでいないとき --}}
    <select x-show="paid === ''" @if ($currentPaid !== '') style="display: none" @endif disabled
            class="w-full rounded-md border-gray-200 bg-gray-100 shadow-sm text-sm text-gray-400">
        <option>先に「会員」か「ビジター」を選んでください</option>
    </select>

    @error('plan_type')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror

    {{-- 会員区分（料金の区分）。利用タイプと合わせて料金が決まるので、すぐ下に置く --}}
    <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">
        会員区分（一般・シニア・学生） <span class="text-xs text-gray-500">※利用タイプを選ぶときは必須</span>
    </label>
    <select name="category"
            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-slate-500 focus:border-slate-500">
        <option value="">選択してください</option>
        @foreach (\App\Enums\MemberCategory::cases() as $case)
            <option value="{{ $case->value }}" @selected($currentCategory === $case->value)>
                {{ $case->label() }}
            </option>
        @endforeach
    </select>
    @error('category')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
