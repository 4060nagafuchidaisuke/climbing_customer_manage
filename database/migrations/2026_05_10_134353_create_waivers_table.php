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
        Schema::create('waivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete(); // 会員ID
            $table->string("version")->nullable(); // 同意書のバージョン
            $table->timestamp("signed_at"); // 署名日時
            $table->string("signature_path")->nullable(); // 署名データの保存先（将来、電子化用）
            $table->boolean("is_minor_signed")->default(false); // 未成年同意フラッグ
            $table->string("guardian_name")->nullable(); // 保護者氏名
            $table->index('member_id'); // 未署名者確認
            $table->index('signed_at'); // 未署名者確認
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waivers');
    }
};
