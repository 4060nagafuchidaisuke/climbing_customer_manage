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
        Schema::create('member_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete(); // 会員ID
            $table->string("plan_type"); // 契約プラン種別(月額・半年・年間・都度)
            $table->string("category"); // 会員区分(general/student)
            $table->date("start_date"); // 契約開始日
            $table->date("end_date"); // 契約終了日
            $table->boolean("is_first_registration")->default(false); // 初回登録支払い済フラッグ
            $table->integer("price"); // 契約金額
            $table->string("status")->default('active'); // 契約状態（active/expired/cancelled）
            // 検索機能
            $table->index("member_id");
            $table->index("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_plans');
    }
};
