<x-app-layout
    background="images/Profile_Details_.webp"
    bgPosition="center bottom"
    bgSize="50%"
>
    <x-slot name="header">
        <div class="relative flex items-center">
            <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                集計
            </h2>
            <span class="ml-auto text-sm text-gray-500">
                {{ now()->isoFormat('YYYY年M月D日（ddd）HH:mm') }} 現在
            </span>
        </div>
    </x-slot>

    {{-- 検索機能 --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <form method="GET" action="{{ route('sales.index') }}" class="flex items-end gap-3 bg-white/80 backdrop-blur-sm rounded-xl shadow p-4">
            <div>
                <label class="block text-xs text-gray-500">開始日</label>
                <input type="date" name="from" value="{{ $from }}" class="border rounded px-2 py-1">
            </div>
            <div>
                <label class="block text-xs text-gray-500">終了日</label>
                <input type="date" name="to" value="{{ $to }}" class="border rounded px-2 py-1">
            </div>
            <button type="submit" class="bg-gray-800 text-white rounded px-4 py-2">絞り込む</button>
            <a href="{{ route('sales.index') }}" class="text-gray-500 px-2 py-2">クリア</a>
        </form>
    </div>

    {{-- 集計結果のサマリ（総売り上げ、契約件数、平均単価）の表示 --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow p-5">
                <div class="text-xs text-gray-500">総売上</div>
                <div class="text-2xl font-bold text-gray-800">¥{{ number_format($summary->total) }}</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow p-5">
                <div class="text-xs text-gray-500">契約件数</div>
                <div class="text-2xl font-bold text-gray-800">{{ number_format($summary->count) }} 件</div>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow p-5">
                <div class="text-xs text-gray-500">平均単価</div>
                <div class="text-2xl font-bold text-gray-800">
                    ¥{{ number_format($summary->count > 0 ? round($summary->total / $summary->count) : 0) }}
                </div>
            </div>
        </div>
    </div>

   {{-- 集計結果のタブ化 --}}
    <div x-data="{ tab: 'daily' }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- タブボタン --}}
        <div class="flex gap-2 mb-4">
            <button @click="tab='daily'"   :class="tab==='daily'   ? 'bg-gray-800 text-white' : 'bg-white/80 text-gray-600'" class="px-4 py-2 rounded-lg shadow">日次</button>
            <button @click="tab='monthly'" :class="tab==='monthly' ? 'bg-gray-800 text-white' : 'bg-white/80 text-gray-600'" class="px-4 py-2 rounded-lg shadow">月次</button>
            <button @click="tab='plan'"    :class="tab==='plan'    ? 'bg-gray-800 text-white' : 'bg-white/80 text-gray-600'" class="px-4 py-2 rounded-lg shadow">プラン別</button>
            <button @click="tab='tier'"    :class="tab==='tier'    ? 'bg-gray-800 text-white' : 'bg-white/80 text-gray-600'" class="px-4 py-2 rounded-lg shadow">料金区分</button>
        </div>

        {{-- 日次 --}}
        <div x-show="tab==='daily'">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left">日付</th>
                            <th class="px-6 py-3 text-right">売上</th>
                            <th class="px-6 py-3 text-right">件数</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($daily as $row)
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('sales.details', ['date' => $row->start_date->format('Y-m-d')]) }}"
                                    class="text-sky-600 hover:underline">
                                        {{ $row->start_date->format('Y/m/d') }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right">¥{{ number_format($row->total) }}</td>
                                <td class="px-6 py-4 text-right">{{ $row->count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">売上なし</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 月次 --}}
        <div x-show="tab==='monthly'">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left">月</th>
                            <th class="px-6 py-3 text-right">売上</th>
                            <th class="px-6 py-3 text-right">件数</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($month as $row)
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('sales.details', ['month' => $row->month, 'from' => $from, 'to' => $to]) }}"
                                        class="text-sky-600 hover:underline">{{ $row->month }}</a>
                                </td>
                                <td class="px-6 py-4 text-right">¥{{ number_format($row->total) }}</td>
                                <td class="px-6 py-4 text-right">{{ $row->count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">売上なし</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- プラン別 --}}
        <div x-show="tab==='plan'">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left">プラン別</th>
                            <th class="px-6 py-3 text-right">売上</th>
                            <th class="px-6 py-3 text-right">件数</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($byPlanType as $row)
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('sales.details', ['plan_type' => $row->plan_type, 'from' => $from, 'to' => $to]) }}"
                                    class="text-sky-600 hover:underline">{{ \App\Enums\PlanType::from($row->plan_type)->label() }}</a>
                                </td>
                                <td class="px-6 py-4 text-right">¥{{ number_format($row->total) }}</td>
                                <td class="px-6 py-4 text-right">{{ $row->count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">売上なし</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 料金区分 --}}
        <div x-show="tab==='tier'">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left">料金区分</th>
                            <th class="px-6 py-3 text-right">売上</th>
                            <th class="px-6 py-3 text-right">件数</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($byPriceTier as $row)
                            <tr>
                                <td class="px-6 py-4">
                                    <a href="{{ route('sales.details', ['price_tier' => $row->price_tier, 'from' => $from, 'to' => $to]) }}"
                                    class="text-sky-600 hover:underline">{{ \App\Enums\PriceTier::from($row->price_tier)->label() }}</a>
                                </td>
                                <td class="px-6 py-4 text-right">¥{{ number_format($row->total) }}</td>
                                <td class="px-6 py-4 text-right">{{ $row->count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">売上なし</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>