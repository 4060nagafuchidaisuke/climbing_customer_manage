<x-app-layout
    background="images/NewMember.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                会員管理
            </h2>

            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('members.register_qr') }}"
                class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                    QRで新規登録
                </a>
                
                <a href="{{ route('members.create') }}"
                class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                    ＋ 新規登録
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- 検索フォーム --}}
            <form method="GET" action="{{ route('members.index') }}" class="mb-6">
                <div class="flex gap-3">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="会員番号・氏名・カナで検索"
                           class="flex-1 rounded-md border-gray-300 shadow-sm text-sm focus:ring-slate-500 focus:border-slate-500">
                    <button type="submit"
                            class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                        検索
                    </button>
                    @if(request('search'))
                        <a href="{{ route('members.index') }}"
                           class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300 transition">
                            クリア
                        </a>
                    @endif
                </div>
            </form>

            {{-- 件数表示 --}}
            <p class="text-sm text-gray-500 mb-3">
                全 {{ $members->total() }} 件
                @if(request('search'))
                    ／「{{ request('search') }}」の検索結果
                @endif
            </p>

            {{-- テーブル --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">会員番号</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">氏名</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">カナ</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">プラン</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">登録日</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">前回来店日時</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($members as $member)
                            <tr class="hover:bg-gray-50 transition">

                                {{--会員番号--}}
                                <td class="px-4 py-3 font-mono text-gray-600">
                                    {{ $member->member_code }}
                                </td>

                                {{--氏名--}}
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $member->last_name }} {{ $member->first_name }}

                                    {{--未成年バッジ--}}
                                    @if($member->is_minor)
                                        <span class="ms-1 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">未成年</span>
                                    @endif
                                </td>

                                {{--氏名（カナ）--}}
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $member->last_name_kana }} {{ $member->first_name_kana }}
                                </td>

                                {{--プラン--}}
                                <td class="px-4 py-3">
                                    @if($member->activePlan)
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                            {{ $member->activePlan->plan->plan_type->label() }}
                                        </span>
                                    @else
                                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">
                                            なし
                                        </span>
                                    @endif
                                </td>

                                {{--登録日--}}
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $member->registered_at?->format('Y/m/d/H:m') ?? '—' }}
                                </td>

                                {{--前回来店日--}}
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $member->created_at->format('Y/m/d') }}
                                </td>

                                {{--詳細--}}
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('members.show', $member) }}"
                                       class="text-slate-600 hover:text-slate-900 text-xs font-medium">
                                        詳細 →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                    会員が見つかりませんでした
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーション --}}
            <div class="mt-4">
                {{ $members->links() }}
            </div>

        </div>
    </div>
</x-app-layout>