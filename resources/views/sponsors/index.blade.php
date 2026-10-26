<x-app-layout
    background="images/NewMember.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                広告管理画面
            </h2>
            <a href="{{ route('sponsors.create') }}"
            class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                ＋ 新規登録
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- テーブル --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル（スポンサー名）</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メディアのプレビュー</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">表示秒数</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">表示順</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">掲載期間</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">公開状態</th>
                            {-- <th class="px-4 py-3"></th> --}
                            <th class="px-4 py-3"></th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($sponsors as $sponsor)
                            <tr class="hover:bg-gray-50 transition">

                                {{--タイトル（スポンサー名）--}}
                                <td class="px-4 py-3  text-gray-600">
                                    {{ $sponsor->title }}
                                </td>

                                {{--メディアのプレビュー--}}
                            
                                <td class=" font-medium text-gray-900">
                                   
                                        @if($sponsor->media_type ===  \App\Enums\SponsorMediaType::VIDEO)
                                            <video class="h-16" src="{{ $sponsor->media_url }}" controls></video> 
                                        @elseif($sponsor->media_type === \App\Enums\SponsorMediaType::IMAGE)
                                            <img class="h-16" src="{{ $sponsor->media_url }}">
                                        @endif
                                </td>

                                {{-- 表示秒数 --}}
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $sponsor->display_seconds}}
                                </td>

                                {{-- 表示順 --}}
                                <td class="px-4 py-3">
                                    {{ $sponsor->sort_order}}
                                </td>

                                {{-- 掲載期間 --}}
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $sponsor->start_date?->format('Y/m/d') ?? '指定なし' }}〜{{ $sponsor->end_date?->format('Y/m/d') ?? '指定なし' }}
                                </td>

                                {{-- 公開状態 --}}
                                <td class="px-4 py-3 text-gray-500">
                                    @if($sponsor->is_active)
                                        <p class="text-blue-700 font-medium text-sm mb-2">公開中</p>
                                    @else
                                        <p class="text-red-700 font-medium text-sm mb-2">非公開</p>
                                    @endif
                                </td>

                                {{-- 編集ボタン 
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('sponsors.edit', $sponsor) }}" method="POST" class="inline">
                                        @csrf
                                        @method('EDIT')
                                            <button type="submit"
                                                onclick="return confirm('「{{ $sponsor->title }}」を削除しますか？')"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                登録内容の変更→
                                            </button>
                                    </form>
                                </td>--}}

                                {{-- 削除ボタン --}}
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('sponsors.destroy', $sponsor) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('「{{ $sponsor->title }}」を削除しますか？')"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                削除😢
                                            </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                    ❕広告が登録されていません❕
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーション --}}
            {{-- <div class="mt-4">
                {{ $sponsors->links() }}
            </div> --}}
        </div>
    </div>
</x-app-layout>