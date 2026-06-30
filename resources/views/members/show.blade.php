<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('members.index') }}"
                   class="text-gray-400 hover:text-gray-600 text-sm">← 一覧に戻る</a>
                <h2 class="font-semibold text-xl text-gray-800">
                    {{ $member->full_name }}
                </h2>
                <span class="text-sm text-gray-500 font-mono">{{ $member->member_code }}</span>

                @if($member->caution_flag)
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                        ⚠ 注意
                    </span>
                @endif
                @if($member->is_minor)
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                        未成年
                    </span>
                @endif
            </div>
            <a href="{{ route('members.edit', $member) }}"
               class="px-4 py-2 bg-slate-700 text-white text-sm rounded-md hover:bg-slate-600 transition">
                編集
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- 注意メモ（caution_flag が true の場合） --}}
            @if($member->caution_flag && $member->caution_notes)
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    ⚠ <strong>注意事項：</strong>{{ $member->caution_notes }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- 左カラム（基本情報・来店履歴） --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- 基本情報 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">基本情報</h3>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500">氏名（カナ）</dt>
                                <dd class="text-gray-800">{{ $member->full_name_kana }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">生年月日</dt>
                                <dd class="text-gray-800">
                                    {{ $member->birth_date?->format('Y年m月d日') }}
                                    <span class="text-gray-400 text-xs">（{{ $member->age }}歳）</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">電話番号</dt>
                                <dd class="text-gray-800">{{ $member->phone ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">メールアドレス</dt>
                                <dd class="text-gray-800">{{ $member->email ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">郵便番号</dt>
                                <dd class="text-gray-800">{{ $member->postal_code ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">住所</dt>
                                <dd class="text-gray-800">{{ $member->address ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">クライミングレベル</dt>
                                <dd class="text-gray-800">{{ $member->climbing_level ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">バーコード</dt>
                                <dd class="font-mono text-gray-600">{{ $member->barcode ?? '—' }}</dd>
                            </div>
                            @if($member->injury_notes)
                                <div class="col-span-2">
                                    <dt class="text-gray-500">負傷・注意事項</dt>
                                    <dd class="text-gray-800">{{ $member->injury_notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- 緊急連絡先 --}}
                    @if($member->emergency_name)
                        <div class="bg-white/80 rounded-lg shadow p-6">
                            <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">緊急連絡先</h3>
                            <dl class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <dt class="text-gray-500">氏名</dt>
                                    <dd class="text-gray-800">{{ $member->emergency_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">続柄</dt>
                                    <dd class="text-gray-800">{{ $member->emergency_relation ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">電話番号</dt>
                                    <dd class="text-gray-800">{{ $member->emergency_phone ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif

                    {{-- 未成年：保護者情報 --}}
                    @if($member->is_minor)
                        <div class="bg-blue-50/80 rounded-lg shadow p-6 border border-blue-100">
                            <h3 class="font-semibold text-blue-700 mb-4 pb-2 border-b border-blue-200">
                                保護者情報
                            </h3>
                            <dl class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <dt class="text-blue-500">保護者氏名</dt>
                                    <dd class="text-gray-800">{{ $member->guardian_name ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-blue-500">保護者電話番号</dt>
                                    <dd class="text-gray-800">{{ $member->guardian_phone ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif

                    {{-- 来店履歴 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">
                            来店履歴
                            <span class="text-xs text-gray-400 font-normal ml-2">最新10件</span>
                        </h3>
                        @if($member->visits->isEmpty())
                            <p class="text-sm text-gray-400 text-center py-4">来店履歴がありません</p>
                        @else
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-gray-500 border-b">
                                        <th class="pb-2">入店日時</th>
                                        <th class="pb-2">退店日時</th>
                                        <th class="pb-2">種別</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($member->visits as $visit)
                                        <tr class="py-2">
                                            <td class="py-2 text-gray-700">
                                                {{ $visit->check_in_at?->format('Y/m/d H:i') }}
                                            </td>
                                            <td class="py-2 text-gray-500">
                                                @if($visit->check_out_at)
                                                    {{ $visit->check_out_at->format('H:i') }}
                                                @else
                                                    <span class="text-green-600 font-medium">在店中</span>
                                                @endif
                                            </td>
                                            <td class="py-2">
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                                                    {{ $visit->visit_type?->value ?? '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>

                {{-- 右カラム（プラン・メモ・誓約書） --}}
                <div class="space-y-6">

                    {{-- プラン情報 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">プラン情報</h3>
                        @forelse($member->memberPlans as $plan)
                            <div class="mb-3 p-3 rounded-md
                                {{ $plan->status->value === 'active' ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-800">
                                        {{ $plan->plan_type?->value ?? '—' }}
                                    </span>
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                        {{ $plan->status->value === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' }}">
                                        {{ $plan->status->value }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $plan->start_date?->format('Y/m/d') }} 〜
                                    {{ $plan->end_date?->format('Y/m/d') ?? '期限なし' }}
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-2">プランなし</p>
                        @endforelse
                    </div>

                    {{-- スタッフメモ --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">スタッフメモ</h3>
                        @forelse($member->staffNotes as $note)
                            <div class="mb-3 p-3 rounded-md
                                {{ $note->is_alert ? 'bg-red-50 border border-red-200' : 'bg-gray-50' }}">
                                <p class="text-sm {{ $note->is_alert ? 'text-red-700' : 'text-gray-700' }}">
                                    @if($note->is_alert) ⚠ @endif
                                    {{ $note->note }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $note->created_at->format('Y/m/d') }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-2">メモなし</p>
                        @endforelse
                    </div>

                    {{-- 誓約書 --}}
                    <div class="bg-white/80 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-700 mb-4 pb-2 border-b">誓約書</h3>
                        @forelse($member->waivers as $waiver)
                            <div class="text-sm">
                                @if($waiver->signed_at)
                                    <span class="text-green-600 font-medium">✓ 署名済み</span>
                                    <span class="text-gray-400 text-xs ml-2">
                                        {{ $waiver->signed_at->format('Y/m/d') }}
                                    </span>
                                @else
                                    <span class="text-red-500 font-medium">✗ 未署名</span>
                                @endif
                                <div class="text-xs text-gray-400 mt-1">
                                    ver.{{ $waiver->version }}
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-2">誓約書なし</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>