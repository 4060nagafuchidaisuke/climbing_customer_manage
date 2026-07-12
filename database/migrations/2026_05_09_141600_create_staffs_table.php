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
        Schema::create('staffs', function (Blueprint $table) {
            $table->id(); // 内部キー
            $table->string('name'); // スタッフ名
            $table->string('staff_code', 10)->unique(); // staffID
            $table->string('email')->nullable()->unique(); // email
            $table->string('phone')->nullable();   // 電話番号
            $table->string('address')->nullable(); // 住所
            $table->string('password'); // ログインパスワード
            $table->string('role')->default('receptionist'); // 権限(admin/ receptionist)
            $table->boolean('is_active')->default(true); // 有効フラッグ
            $table->softDeletes(); // 論理削除
            $table->timestamp('last_login_at')->nullable(); // 最終ログイン
            $table->rememberToken(); // ログインの保持

            // 検索機能
            $table->index('role');
            $table->index('is_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
