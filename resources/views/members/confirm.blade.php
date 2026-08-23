{{-- 店員が確認する画面用 --}}
<x-app-layout
    background="images/NewMember.webp"
    bgPosition="center bottom"
    bgSize="100%"
>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="absolute left-1/2 -translate-x-1/2 font-semibold text-xl text-gray-800">
                    入力内容の確認 | HAZY BOULDER
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- 見出し --}}
                <h1 class="font-semibold text-xl text-gray-800 mb-2">入力内容の確認</h1>
                <p class="text-sm text-gray-500 mb-6">以下の内容でよろしければ「この内容で登録する」を押してください。</p>

                    <div class="space-y-6">

                        {{-- 基本情報 --}}
                        <div class="bg-white/80 rounded-lg shadow p-6">
                            <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">基本情報</h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">

                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">姓</dt>
                                    <dd class="text-gray-800">{{ $data['last_name'] }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">名</dt>
                                    <dd class="text-gray-800">{{ $data['first_name'] }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">姓（カナ）</dt>
                                    <dd class="text-gray-800">{{ $data['last_name_kana'] }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">名（カナ）</dt>
                                    <dd class="text-gray-800">{{ $data['first_name_kana'] }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">生年月日</dt>
                                    <dd class="text-gray-800">{{ $data['birth_date'] }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">性別</dt>
                                    <dd class="text-gray-800">
                                        {{ \App\Enums\Gender::tryFrom($data['gender'] ?? '')?->label() ?? '未選択' }}
                                    </dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">電話番号</dt>
                                    <dd class="text-gray-800">{{ $data['phone'] }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">メールアドレス</dt>
                                    <dd class="text-gray-800">{{ $data['email'] ?? '未入力' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">郵便番号</dt>
                                    <dd class="text-gray-800">{{ $data['postal_code'] ?? '未入力' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">クライミングレベル</dt>
                                    <dd class="text-gray-800">
                                        {{ \App\Enums\ClimbingLevel::tryFrom($data['climbing_level'] ?? '')?->label() ?? '未選択' }}
                                    </dd>
                                </div>
                                <div class="flex md:col-span-2">
                                    <dt class="w-32 text-gray-500 shrink-0">住所</dt>
                                    <dd class="text-gray-800">{{ $data['address'] }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-32 text-gray-500 shrink-0">職業</dt>
                                    <dd class="text-gray-800">{{ $data['occupation'] ?? '未入力' }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- 緊急連絡先 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">緊急連絡先</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div class="flex">
                                <dt class="w-32 text-gray-500 shrink-0 mr-2">氏名</dt>
                                <dd class="text-gray-800">{{ $data['emergency_name'] }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-500 shrink-0 mr-2">続柄</dt>
                                <dd class="text-gray-800">{{ $data['emergency_relation'] ?? '未入力' }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-500 shrink-0 mr-2">電話番号</dt>
                                <dd class="text-gray-800">{{ $data['emergency_phone'] }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- 未成年・保護者情報 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">未成年・保護者情報</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div class="flex">
                                <dt class="w-32 text-gray-500 shrink-0 mr-2">未成年</dt>
                                <dd class="text-gray-800">{{ !empty($data['is_minor']) ? 'はい' : 'いいえ' }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-500 shrink-0 mr-2">保護者氏名</dt>
                                <dd class="text-gray-800">{{ $data['guardian_name'] ?? '未入力' }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 text-gray-500 shrink-0 mr-2">保護者電話番号</dt>
                                <dd class="text-gray-800">{{ $data['guardian_phone'] ?? '未入力' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- 注意フラグ --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">スタッフ注意事項</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div class="flex">
                                <dt class="w-32 text-gray-500 shrink-0">注意フラグ</dt>
                                <dd class="text-gray-800">{{ !empty($data['caution_flag']) ? 'あり' : 'なし' }}</dd>
                            </div>
                            <div class="flex md:col-span-2">
                                <dt class="w-32 text-gray-500 shrink-0">注意メモ</dt>
                                <dd class="text-gray-800">{{ $data['caution_notes'] ?? '未入力' }}</dd>
                            </div>
                        </dl>
                    </div>
                    {{-- 免責事項 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">免責事項</h3>
                        <p class="text-sm text-green-700 font-medium">
                            免責事項：免責事項の内容を確認しました ✓
                        </p>
                    </div>

                    {{-- ボタン --}}
                    <div class="flex justify-between items-center">
                        {{-- 修正する（★3：session の署名付きURLで create へ戻る） --}}
                        <a href="{{ route('members.create') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md
                                hover:bg-gray-300 transition">
                            修正する
                        </a>

                        {{-- この内容で登録する（store へ POST／hidden 不要＝session から読む） --}}
                        <form method="POST" action="{{ route('members.store') }}">
                            @csrf
                            <button type="submit"
                                    class="px-6 py-2 bg-slate-700 text-white text-sm rounded-md
                                        hover:bg-slate-600 transition font-medium">
                                この内容で登録する
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>