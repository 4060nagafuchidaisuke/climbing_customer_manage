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
            $table->string('image_path'); // 画像の保存パス
            $table->string('link_url')->nullable(); // クリック先のurl
            $table->boolean('is_active'); // 表示するかどうか
            $table->integer('display_seconds'); // 表示秒数
            $table->integer('sort_order');// 表示順
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
