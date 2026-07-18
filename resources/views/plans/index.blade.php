<x-app-layout
    background="images/Profile_Details_.webp"
    bgPosition="center bottom"
    bgSize="70%"
>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 tracking-wide">料金マスター</h2>
            <span class="text-sm text-gray-500" id="clock"></span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <table class="min-w-full bg-white rounded-lg shadow overflow-hidden text-sm">
                <thead class="bg-gray-100 text-xs text-gray-600 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">プラン種別</th>
                        @foreach (\App\Enums\PriceTier::cases() as $tier)
                            <th class="px-4 py-3 text-center">{{ $tier->label() }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach (\App\Enums\PlanType::cases() as $type)
                        <tr class="hover:bg-gray-50 transition">
                            <th class="px-4 py-3 text-left font-medium text-gray-700 bg-gray-50">
                                {{ $type->label() }}
                            </th>

                            @foreach (\App\Enums\PriceTier::cases() as $tier)
                                @php
                                    $plan = $plansByKey[$type->value . '|' . $tier->value] ?? null;
                                @endphp
                                <td class="px-4 py-3 text-center">
                                    @if ($plan)
                                        <div class="font-semibold text-gray-800">
                                            ¥{{ number_format($plan->price) }}
                                        </div>
                                        @unless ($plan->is_active)
                                            <div class="text-xs text-red-500">無効</div>
                                        @endunless
                                        <a href="{{ route('plans.edit', $plan) }}"
                                           class="text-xs text-slate-500 hover:text-slate-800">
                                            編集
                                        </a>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</x-app-layout>