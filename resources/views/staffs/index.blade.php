<x-app-layout
    background="images/SystemSetting.webp"
    bgPosition="center bottom"
    bgSize="50%"
>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 tracking-wide ">スタッフ管理</h2>
            <div class="flex items-center gap-2">
                @can('create', App\Models\Staff::class)
                    <a href="{{ route('staffs.create') }}"
                    class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                        ＋ 新規登録
                    </a>
                @endcan
                @can('viewAny', App\Models\Staff::class)
                    <a href="{{ route('staffs.trashed') }}"
                    class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                        🗑 削除履歴
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-sm text-gray-500 mb-3">全 {{ $staffs->total() }} 件</p>

                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">氏名</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">staffID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">電話番号</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">権限</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">最終ログイン</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($staffs as $staff)
                                <tr class="hover:bg-gray-50">
                                    {{-- Staff名 --}}
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $staff->name }}
                                    </td>

                                    {{-- staffID--}}
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $staff->staff_code }}
                                    </td>

                                    {{--電話番号--}}
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $staff->phone }}
                                    </td>

                                    {{-- 権限 --}}
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $staff->role->label() }}
                                    </td>

                                    {{-- 状態 --}}
                                    <td class="px-6 py-4 text-gray-600">
                                        @if($staff->is_active)
                                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">有効</span>
                                        @else
                                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">無効</span>
                                        @endif
                                    </td>
                                
                                    {{-- 最終ログイン --}}
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $staff->last_login_at?->format('Y/m/d H:i')?? '-' }}
                                    </td>

                                     {{--編集画面へ遷移--}}
                                    <td class="px-6 py-4 text-right">
                                        @can('update', $staff)
                                            <a href="{{ route('staffs.edit', $staff) }}"
                                            class="inline-block px-4 py-2 bg-slate-700 text-white text-xs font-medium rounded-lg hover:bg-slate-600 transition">
                                                編集
                                            </a>
                                        @endcan
                                    </td>

                                    {{-- 削除ボタン --}}
                                    <td class="px-6 py-4 text-right">
                                        @can('delete', $staff)
                                            <form action="{{ route('staffs.destroy', $staff) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    onclick="return confirm('「{{ $staff->name }}」を削除しますか？')"
                                                    class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                    削除😢
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9">スタッフが登録されていません</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            <div class="mt-4">
                {{ $staffs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
