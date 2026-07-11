<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            在店中
            <span class="ml-2 bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                {{ $activeVisits->count() }}名
            </span>
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- フラッシュメッセージ --}}
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow overflow-hidden">
            @if($activeVisits->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <p class="text-5xl mb-4">🧗</p>
                    <p class="text-lg">現在在店中の会員はいません</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 border-b">
                            <th class="px-6 py-3">会員名</th>
                            <th class="px-6 py-3">カナ</th>
                            <th class="px-6 py-3">プラン</th>
                            <th class="px-6 py-3">入店時刻</th>
                            <th class="px-6 py-3">滞在時間</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($activeVisits as $visit)
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

                                {{--滞在時間--}}
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $visit->check_in_at->diffForHumans(now(), true) }}
                                </td>

                                {{-- 退店指示ボタン --}}
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('visits.checkout', $visit) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-4 py-2 rounded-lg transition">
                                            退店
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // 購読コード()
        document.addEventListener('DOMContentLoaded', () => {
            window.Echo.channel('visits')
                .listen('CheckedIn', (e)=>{console.log('CheckedIn received!', e); location.reload();})
                .listen('CheckedOut', (e)=>{location.reload();});
            setInterval(() => {
                location.reload();
            }, 10000);
        });
    </script>
    @endpush


</x-app-layout>