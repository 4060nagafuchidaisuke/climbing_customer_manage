<x-app-layout
background="images/Profile_Details_.webp"
    bgPosition="center bottom"
    bgSize="70%"
>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            {{ $heading }} の契約明細
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <a href="{{ route('sales.index') }}" class="text-sky-600 hover:underline">← 集計に戻る</a>

        <div class="mt-4 bg-white/90 rounded-2xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">会員</th>
                        <th class="px-6 py-3 text-left">プラン</th>
                        <th class="px-6 py-3 text-right">金額</th>
                        <th class="px-6 py-3 text-left">契約日</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($details as $mp)
                        <tr>
                            <td class="px-6 py-4">{{ $mp->member->full_name }}</td>
                            <td class="px-6 py-4">{{ $mp->plan->plan_type->label() }}</td>
                            <td class="px-6 py-4 text-right">¥{{ number_format($mp->price_paid) }}</td>
                            <td class="px-6 py-4">{{ $mp->start_date->format('Y/m/d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">明細なし</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>