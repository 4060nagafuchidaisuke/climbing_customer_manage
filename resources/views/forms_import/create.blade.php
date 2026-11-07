<x-app-layout
    background="images/NewMember.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 text-center">
            Microsoft Forms の会員データ取り込み
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- バリデーションエラー表示 --}}
            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 font-medium text-sm mb-2">入力内容を確認してください：</p>
                    <ul class="text-red-600 text-sm list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 前回までの取り込み状況（今回何人増えたかを確かめる目安） --}}
            <div class="bg-white/90 rounded-lg shadow px-6 py-4 text-sm text-gray-700">
                @if($lastImport)
                    <span class="font-semibold">前回までの取り込み：</span>
                    {{ $lastImport['last_imported_at']->format('Y/m/d H:i') }}
                    ／ 回答ID {{ $lastImport['last_response_id'] }} まで
                    ／ 合計 {{ $lastImport['count'] }}人
                @else
                    <span class="font-semibold">前回までの取り込み：</span>まだありません
                @endif
            </div>

            {{-- 取り込み結果 --}}
            @if($result)
                <div class="bg-white/90 rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">
                        {{ $result['dry_run'] ? 'チェック結果（まだ登録していません）' : '取り込み結果' }}
                    </h3>

                    <ul class="text-sm text-gray-700 space-y-1 mb-4">
                        <li>{{ $result['dry_run'] ? '新しく登録できる件数' : '新しく登録した件数' }}：<span class="font-bold">{{ $result['imported'] }}件</span></li>
                        <li>前回までに取り込み済みのため飛ばした件数：{{ $result['already_imported'] }}件（上書きはしていません）</li>
                        <li>取り込めなかった行：<span class="{{ $result['errors'] ? 'text-red-600 font-bold' : '' }}">{{ count($result['errors']) }}行</span></li>
                        <li>要確認の行：{{ count($result['warnings']) }}行</li>
                    </ul>

                    @if($result['errors'])
                        <p class="text-red-700 font-medium text-sm mb-1">取り込めなかった行（Forms の内容を直すか、来店時にアプリで登録してください）</p>
                        <table class="w-full text-sm mb-4 border">
                            <thead class="bg-red-50">
                                <tr><th class="px-2 py-1 w-16 text-left">行</th><th class="px-2 py-1 text-left">理由</th></tr>
                            </thead>
                            <tbody>
                                @foreach($result['errors'] as $row => $messages)
                                    <tr class="border-t">
                                        <td class="px-2 py-1">{{ $row }}</td>
                                        <td class="px-2 py-1">{{ implode(' / ', $messages) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($result['warnings'])
                        <p class="text-amber-700 font-medium text-sm mb-1">要確認（取り込みはしています）</p>
                        <table class="w-full text-sm border">
                            <thead class="bg-amber-50">
                                <tr><th class="px-2 py-1 w-16 text-left">行</th><th class="px-2 py-1 text-left">内容</th></tr>
                            </thead>
                            <tbody>
                                @foreach($result['warnings'] as $row => $messages)
                                    <tr class="border-t">
                                        <td class="px-2 py-1">{{ $row }}</td>
                                        <td class="px-2 py-1">{{ implode(' / ', $messages) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    {{-- チェックしたファイルをそのまま取り込む（ファイルを選び直さなくてよいように、サーバーで預かっている） --}}
                    @if($result['dry_run'] && $pending)
                        <div class="mt-6 pt-4 border-t text-center">
                            <p class="text-sm text-gray-700 mb-3">
                                チェックしたファイル：<span class="font-medium">{{ $pending['name'] }}</span><br>
                                <span class="text-xs text-gray-500">取り込めなかった行は登録されません。ファイルは1時間たつと自動で削除されます。</span>
                            </p>
                            <div class="flex justify-center gap-3">
                                <form method="POST" action="{{ route('forms-import.cancel') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-6 py-2 bg-white border border-gray-300 text-gray-700 text-lg rounded-md hover:bg-gray-50 transition">
                                        取り消す
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('forms-import.confirm') }}"
                                      onsubmit="return confirm('{{ $result['imported'] }}件を会員として登録します。よろしいですか？');">
                                    @csrf
                                    <button type="submit"
                                            class="px-6 py-2 bg-slate-700 text-white text-lg rounded-md hover:bg-slate-600 transition">
                                        この内容で取り込む
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- アップロード画面（チェック済みのファイルを預かっている間は「取り込む／取り消す」を選んでもらうので出さない） --}}
            @unless($pending)
            <div class="bg-white/80 rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">★取り込むファイルの選択</h3>

                <ol class="text-sm text-gray-600 list-decimal list-inside space-y-1 mb-4">
                    <li>Microsoft Forms の「回答」タブから Excel をダウンロードします。</li>
                    <li>ダウンロードしたファイル（.xlsx）をそのまま選んでください。</li>
                    <li>まず「チェックのみ」で確認し、結果を見てから「この内容で取り込む」を押してください。</li>
                    <li>一度取り込んだ回答は、何度アップロードしても二重には登録されません。</li>
                </ol>

                <form method="POST" action="{{ route('forms-import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 mb-1">ファイル（.xlsx / .csv）<span class="text-red-500">*</span></label>
                    <input type="file" name="file" accept=".xlsx,.csv">
                    @error('file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <label class="mt-4 flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="dry_run" value="1" checked
                               class="rounded-md border-gray-300 shadow-sm focus:ring-slate-500 focus:border-slate-500">
                        チェックのみ（会員は登録しない）
                    </label>

                    <div class="mt-6 flex justify-center">
                        <button type="submit"
                                class="px-6 py-2 bg-slate-700 text-white text-lg rounded-md hover:bg-slate-600 transition">
                            実行
                        </button>
                    </div>
                </form>
            </div>
            @endunless
        </div>
    </div>
</x-app-layout>
