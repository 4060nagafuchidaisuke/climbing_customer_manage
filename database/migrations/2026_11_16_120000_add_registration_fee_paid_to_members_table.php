<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 登録料を払った会員か、ビジターかを記録する
// registered_at は「登録日」として一覧や Forms 取り込みで使っているので、意味を分けて別の列にする
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // true＝会員（登録料支払い済み）／false＝ビジター／null＝未設定
            // Forms 取り込み・タブレット登録の人は未設定のまま。受付でスタッフが決める
            $table->boolean('registration_fee_paid')->nullable()->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('registration_fee_paid');
        });
    }
};
