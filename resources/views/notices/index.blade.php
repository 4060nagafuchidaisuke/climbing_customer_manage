<x-app-layout 
    background="images/Profile_Details_.webp"
    bgPosition="center bottom"
    bgSize="70%"
>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                お知らせ入力一覧
            </h2>
            <span class="text-sm text-gray-500" id="clock"></span>
            <a href="{{ route('notices.create') }}"
               class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                ＋ 新規作成
            </a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{--お知らせ一覧画面--}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- 件数表示 --}}
                    <p class="text-sm text-gray-500 mb-3">
                        全 {{ $notices->total() }} 件
                        @if(request('search'))
                            ／「{{ request('search') }}」の検索結果
                        @endif
                    </p>

                    {{-- テーブル --}}
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">掲載内容</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">表示順</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">掲載終了日</th>
                                    <th class="px-4 py-3"></th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($notices as $notice)
                                    <tr class="hover:bg-gray-50 transition">

                                        {{-- おしらせのタイトル --}}
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $notice->title }}
                                        </td>

                                        {{-- 掲載内容 --}}
                                        <td class="px-4 py-3 text-gray-500">
                                            {{ $notice->body }}
                                        </td>

                                        {{-- 表示順 --}}
                                        <td class="px-4 py-3 text-gray-500">
                                            {{ $notice->sort_order }} 
                                        </td>

                                        {{-- 掲載終了日 --}}
                                        <td class="px-4 py-3 text-gray-500">
                                            {{ $notice->expires_at?->format('Y/m/d') ?? '無期限'}}
                                        </td>

                                        {{-- 操作・非表示、編集、削除 --}}
                                        {{--削除ボタン--}}
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('notices.destroy', $notice) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    onclick="return confirm('「{{ $notice->title }}」を削除しますか？')"
                                                    class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-4 py-2 rounded-lg transition">
                                                    削除
                                                </button>
                                            </form>
                                        </td>

                                        {{--編集画面へ遷移--}}
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('notices.edit', $notice) }}"
                                            class="text-slate-600 hover:text-slate-900 text-xs font-medium">
                                                編集 →
                                            </a>
                                        </td>
                                    </tr>

                                    {{-- お知らせが無い場合 --}}
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                            お知らせはありません
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

            {{-- ページネーション --}}
            <div class="mt-4">
                {{ $notices->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
