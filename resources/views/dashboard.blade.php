<x-app-layout
    background="images/SystemSetting.webp"
    bgPosition="center bottom"
    bgSize="50%"
>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-700 tracking-wide">
                ダッシュボード
            </h2>
            <span class="text-sm text-gray-300">
                {{ now()->isoFormat('YYYY年M月D日（ddd）HH:mm') }} 現在
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- ── KPI カード ────────────────────────────────── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            {{-- 現在在店中 --}}
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow p-5 flex flex-col gap-1 border-l-4 border-emerald-500">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-widest">現在在店中</span>
                <span class="text-4xl font-black text-gray-800">{{ $currentVisitors }}</span>
                <span class="text-xs text-gray-400">名</span>
            </div>

            {{-- 本日入店 --}}
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow p-5 flex flex-col gap-1 border-l-4 border-sky-500">
                <span class="text-xs font-semibold text-sky-600 uppercase tracking-widest">本日の入店</span>
                <span class="text-4xl font-black text-gray-800">{{ $todayCheckIns }}</span>
                <span class="text-xs text-gray-400">名（退店済 {{ $todayCheckOuts }} 名）</span>
            </div>

            {{-- 総会員数 --}}
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow p-5 flex flex-col gap-1 border-l-4 border-violet-500">
                <span class="text-xs font-semibold text-violet-600 uppercase tracking-widest">総会員数</span>
                <span class="text-4xl font-black text-gray-800">{{ $totalMembers }}</span>
                <span class="text-xs text-gray-400">アクティブプラン {{ $activePlanCount }} 名</span>
            </div>

            {{-- 今月の入店 --}}
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow p-5 flex flex-col gap-1 border-l-4 border-amber-500">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-widest">今月の入店</span>
                <span class="text-4xl font-black text-gray-800">{{ $monthlyVisits }}</span>
                <span class="text-xs text-gray-400">{{ now()->isoFormat('M月') }} 累計</span>
            </div>

        </div>

        {{-- ── 中段：在店中 ＋ 本日ログ ─────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 在店中メンバー --}}
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-700">🧗 現在在店中</h3>
                    <a href="{{ route('visits.index') }}"
                       class="text-xs text-sky-600 hover:underline">全員見る →</a>
                </div>

                @if ($activeVisits->isEmpty())
                    <p class="text-center text-gray-400 py-10 text-sm">現在在店中の会員はいません</p>
                @else
                    <ul class="divide-y divide-gray-50">
                        @foreach ($activeVisits as $visit)
                        <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                {{-- アバター代わりの頭文字 --}}
                                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ mb_substr($visit->member->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $visit->member->full_name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $visit->member->activePlan?->plan_type?->label() ?? '都度利用' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">
                                    {{ $visit->check_in_at->format('H:i') }} 入店
                                </p>
                                <p class="text-xs text-emerald-600 font-medium">
                                    {{ $visit->check_in_at->diffForHumans(null, true) }}
                                </p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- 本日の入退店ログ --}}
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-700">📋 本日の入退店ログ</h3>
                </div>

                @if ($recentVisits->isEmpty())
                    <p class="text-center text-gray-400 py-10 text-sm">本日の入退店記録はありません</p>
                @else
                    <ul class="divide-y divide-gray-50">
                        @foreach ($recentVisits as $visit)
                        <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                {{-- 在店中 or 退店済みバッジ --}}
                                @if ($visit->check_out_at === null)
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 ring-2 ring-emerald-200"></span>
                                @else
                                    <span class="inline-block w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ $visit->member->full_name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $visit->member->member_code }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right text-xs text-gray-500">
                                <p>IN {{ $visit->check_in_at->format('H:i') }}</p>
                                @if ($visit->check_out_at)
                                    <p>OUT {{ $visit->check_out_at->format('H:i') }}</p>
                                @else
                                    <p class="text-emerald-600 font-semibold">在店中</p>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>

        {{-- 本日の新規登録があれば表示 --}}
        @if ($todayNewMembers > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-center gap-3">
            <span class="text-2xl">🎉</span>
            <p class="text-sm text-amber-800 font-medium">
                本日 <strong>{{ $todayNewMembers }}</strong> 名の新規会員が登録されました！
            </p>
        </div>
        @endif

    </div>
</x-app-layout>