<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CheckinController;;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\StaffController;

Route::get('/', function (){
    return view('welcome');
});

// 認証されたスタッフだけが見れる画面
Route::middleware('auth')->group(function (){
    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('members', MemberController::class);

    // 会員の検索
    Route::post('/checkin/search',[VisitController::class, 'search'])->name('visits.search');

    // 在店中画面との結合
    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
    Route::patch('/visits/{visit}/checkout', [VisitController::class, 'checkout'])->name('visits.checkout');

    // 入退店管理
    // 入退店管理画面
    Route::get('/checkin',  [CheckinController::class, 'index'])->name('checkin.index');
    Route::post('/checkin', [CheckinController::class, 'process'])->name('checkin.process');

        // お知らせ画面表示
        Route::resource('notices', NoticeController::class)->except(['show']);

        // スポンサー画面の管理
        Route::resource('sponsors', SponsorController::class)->except(['show', 'edit', 'update']);

    // スタッフ管理
    // 削除スタッフの一覧・復活
    Route::get('staffs/trashed', [StaffController::class, 'trashed'])->name('staffs.trashed');
    Route::patch('staffs/{id}/restore', [StaffController::class, 'restore'])->name('staffs.restore');
    Route::delete('staffs/{id}/force', [StaffController::class, 'forceDelete'])->name('staffs.force-delete');

    Route::resource('staffs', StaffController::class)->except(['show']);

    // ログイン中のスタッフが自分のメールアドレスやパスワードを変更するため
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';
