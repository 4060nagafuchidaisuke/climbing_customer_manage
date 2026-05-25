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
        Schema::create('staff_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete(); // 会員ID
            $table->text('note'); // メモ内容
            $table->boolean('is_alert')->default(false); // 警告表示フラッグ
            $table->foreignId('created_by')->nullable()->constrained('staffs'); // 作成スタッフID
            // 検索機能
            $table->index('member_id');
            $table->index('is_alert');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_notes');
    }
};
