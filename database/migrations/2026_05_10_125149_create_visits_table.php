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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete(); // 会員ID
            $table->timestamp("check_in_at"); // 入店日時
            $table->timestamp("check_out_at")->nullable(); // 退店日時
            $table->string("visit_type")->nullable(); // 利用種別（normal / trial / lesson）
            $table->string("visit_source")->nullable(); // 受付方法（barcode / manual）
            $table->string("checked_in_by")->nullable()->constrained('users'); // 入店受付スタッフID
            $table->string("checked_out_by")->nullable()->constrained('users'); // 退店受付スタッフID
            $table->text("staff_note"); // 受付メモ
            // 検索機能
            $table->index('member_id');
            $table->index('check_in_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
