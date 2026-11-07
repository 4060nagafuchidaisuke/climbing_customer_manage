<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// お店（HAZY BOULDER）の会員登録 Forms に項目と必須をそろえる
// 既存のカラムは消さずに残し、足りないものを足す
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Q5：お住まい（佐賀県内／佐賀県外）。ResidenceArea Enum
            $table->string('residence_area')->nullable()->after('address');

            // Q6：クライミング経験（お客さんの自己申告）。ClimbingExperience Enum
            // スタッフが判断するレベル（climbing_level）とは別に持つ
            $table->string('climbing_experience')->nullable()->after('climbing_level');

            // Forms には住所の質問がなく、緊急連絡先も任意なので、空でもよくする
            $table->string('address')->nullable()->change();
            $table->string('emergency_phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 注意：住所・緊急連絡先が空の会員が残っていると NOT NULL に戻せず失敗する
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['residence_area', 'climbing_experience']);

            $table->string('address')->nullable(false)->change();
            $table->string('emergency_phone')->nullable(false)->change();
        });
    }
};
