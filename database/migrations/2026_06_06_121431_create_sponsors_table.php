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
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // スポンサー名
            $table->string('media_path'); // 動画・画像の保存パス
            $table->string('media_type'); // 動画or画像
            $table->boolean('is_active')->default(true); // 表示するかどうか
            $table->integer('display_seconds'); // 表示秒数
            $table->integer('sort_order'); // 表示順
            $table->date('start_date')->nullable(); // 表示開始時期
            $table->date('end_date')->nullable(); // 表示終了時期
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};
