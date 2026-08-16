<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disclaimers', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // 免責事項の内容
            $table->timestamps();
        });

        // 初期データの
        DB::table('disclaimers')->insert([
            'content' => '■当施設はクライミング・ボルダリング専用施設です。',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disclaimers');
    }
};
