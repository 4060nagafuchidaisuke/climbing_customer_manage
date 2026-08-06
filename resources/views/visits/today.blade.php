<x-app-layout
    background="images/StaffManagement.webp"
    bgPosition="center bottom"
    bgSize="80%"
>
    <x-slot name="header">
        <div class="relative flex items-center">
            <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                本日の来店メンバー
            </h2>
            <span class="ml-auto text-sm text-gray-500">
                {{ now()->isoFormat('YYYY年M月D日（ddd）HH:mm') }} 現在
            </span>
        
            <span class="ml-2 bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                {{ $visits->total() }}名
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- フラッシュメッセージ --}}
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow overflow-hidden">
            @if($visits->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <p class="text-5xl mb-4">🧗</p>
                    <p class="text-lg">本日の来店はまだありません</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 border-b">
                            <th class="px-6 py-3">会員名</th>
                            <th class="px-6 py-3">カナ</th>
                            <th class="px-6 py-3">プラン</th>
                            <th class="px-6 py-3">入店時刻</th>
                            <th class="px-6 py-3">退店時刻</th>
                            <th class="px-6 py-3">滞在時間</th>
                            <th class="px-6 py-3">前回来店日時</th>
                            <th class="px-6 py-3"></th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($visits as $visit)
                            <tr class="hover:bg-gray-50">
                                {{--会員名--}}
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $visit->member->full_name }}
                                    @if($visit->member->is_minor)
                                        <span class="ml-1 text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded">未成年</span>
                                    @endif
                                </td>

                                {{--カナ--}}
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $visit->member->full_name_kana }}
                                </td>

                                {{--プラン--}}
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $visit->member->activePlan?->plan->plan_type->label() ?? '－' }}
                                </td>

                                {{--入店時間 --}}
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $visit->check_in_at->format('H:i') }}
                                </td>

                                {{--退店時間 --}}
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $visit->check_out_at?->format('H:i') }}
                                </td>

                                {{--滞在時間--}}
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $visit->check_in_at->diffForHumans(now(), true) }}
                                </td>

                                {{--前回来店日時--}}
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $visit->member->previousVisit?->check_in_at?->format('Y/m/d H:i') ?? '初来店' }}
                                </td>


                                {{--詳細--}}
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('members.show', $visit->member) }}"
                                       class="text-slate-600 hover:text-slate-900 text-xs font-medium">
                                        詳細 →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $visits->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>