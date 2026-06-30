<x-app-layout
    background="images/SystemSetting.png"
    bgPosition="center bottom"
    bgSize="50%"
>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 tracking-wide">削除済みスタッフ一覧</h2>
            @can('viewAny', App\Models\Staff::class)
                <a href="{{ route('staffs.index') }}"
                class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                ← スタッフ一覧へ戻る
                </a>
            @endcan
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
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">削除日時</th>
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

                                    {{-- 削除日時 --}}
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $staff->deleted_at?->format('Y/m/d H:i')?? '-' }}
                                    </td>

                                    {{--復活ボタン--}}
                                    <td class="px-6 py-4 text-right">
                                        @can('restore', $staff)
                                            <form action="{{ route('staffs.restore', $staff) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-800 text-xs font-medium"
                                                        onclick="return confirm('「{{ $staff->name }}」を元に戻しますか？')">
                                                    元に戻す
                                                </button>
                                            </form>
                                          @endcan
                                    </td>

                                    {{-- 完全削除ボタン --}}
                                    <td class="px-6 py-4 text-right">
                                        @can('forceDelete', $staff)
                                            <form action="{{ route('staffs.force-delete', $staff) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    onclick="return confirm('「{{ $staff->name }}」を完全に削除しますか？削除後は元に戻せません。')"
                                                    class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                    完全削除😢
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9">削除されたスタッフはいません</td>
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
