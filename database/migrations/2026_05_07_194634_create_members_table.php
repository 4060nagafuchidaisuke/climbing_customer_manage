<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id(); // 内部キー
            $table->string('member_code')->nullable()->unique(); // 会員番号
            $table->string('barcode')->nullable()->unique(); // バーコード文字列
            $table->string('last_name'); // 姓
            $table->string('first_name'); // 名
            $table->string('last_name_kana'); // 姓カナ
            $table->string('first_name_kana'); // 名カナ
            $table->date('birth_date')->nullable(); // 誕生日
            $table->string('gender')->nullable(); // 性別
            $table->string('phone'); // 電話番号
            $table->string('email')->nullable()->unique(); // メールアドレス
            $table->string('postal_code')->nullable(); // 郵便番号
            $table->string('address'); // 住所
            $table->string('occupation')->nullable(); // 職業
            $table->string('photo_path')->nullable(); // 顔写真保存先
            $table->string('climbing_level')->default('beginner')->nullable();;// クライミングレベル
            $table->text('injury_notes')->nullable(); // 怪我歴
            $table->boolean('caution_flag')->nullable(); // 注意人物フラグ
            $table->text('caution_notes')->nullable(); // 注意事項
            $table->boolean('is_minor')->nullable(); // 未成年フラグ
            $table->string('guardian_name')->nullable(); // 保護者氏名
            $table->string('guardian_phone')->nullable(); // 保護者連絡先
            $table->string('emergency_name')->nullable(); // 緊急連絡先氏名
            $table->string('emergency_relation')->nullable(); // 続柄
            $table->string('emergency_phone'); // 緊急連絡先電話番号
            $table->timestamps(); // 初回登録日・更新日時間
            $table->string('category')->nullable(); // MemberCategory(6区分)
            $table->timestamp('registered_at')->nullable();

            // 検索用インデックス
            $table->index(['last_name', 'first_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
