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
        Schema::table('members', function (Blueprint $table) {
            // Microsoft Forms の回答ID（同じ回答を二重に取り込まないための目印）
            $table->string('forms_response_id')->nullable()->unique()->after('barcode');

            // Forms には名とカナの項目がないため、取り込んだ会員は空になる
            // （次回来店時にスタッフが会員編集画面で入力する）
            $table->string('first_name')->nullable()->change();
            $table->string('last_name_kana')->nullable()->change();
            $table->string('first_name_kana')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 注意：取り込んだ会員（名・カナが null）が残っていると NOT NULL に戻せず失敗する
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['forms_response_id']);
            $table->dropColumn('forms_response_id');

            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name_kana')->nullable(false)->change();
            $table->string('first_name_kana')->nullable(false)->change();
        });
    }
};
