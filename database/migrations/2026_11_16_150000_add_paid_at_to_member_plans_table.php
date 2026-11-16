<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// プランの支払日を記録し、売上は支払日で数える
// それまでは開始日で数えていたので、継続（開始日が先の日付）や開始日の修正で売上の日がずれていた
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_plans', function (Blueprint $table) {
            $table->dateTime('paid_at')->nullable()->after('price_paid');
            $table->index('paid_at'); // 売上の集計で日付ごとに絞り込む
        });

        // 今までの記録は、作成した日時を支払日とみなす（プランの購入と同時に作られている）
        DB::table('member_plans')->whereNull('paid_at')->update(['paid_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_plans', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropColumn('paid_at');
        });
    }
};
