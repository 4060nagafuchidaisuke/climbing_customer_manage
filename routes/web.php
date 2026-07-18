<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\GuestRegistrationController;
use App\Http\Controllers\MemberRegistrationController;
use App\Http\Controllers\MemberPlanController;
use App\Http\Controllers\PlanController; 


Route::get('/', function (){
    return view('welcome');
});

// 認証されたスタッフだけが見れる画面
Route::middleware('auth')->group(function (){
    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

     // 【スタッフ側・要ログイン】QRを表示する画面
        Route::get('/members/register-qr', [QRController::class, 'show'])->name('members.register_qr');

    // 会員情報
    Route::resource('members', MemberController::class);
    
    // 会員の新規登録
    Route::get('members/{member}/register', [MemberRegistrationController::class, 'create'])->name('members.register');

    // 会員の情報変更
    Route::post('members/{member}/register', [MemberRegistrationController::class, 'store'])->name('members.register.store');

    // 会員のプラン変更
    Route::post('members/{member}/plans', [MemberPlanController::class, 'store'])->name('members.plans.store');

    // 来退店受付
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
    
    // ログイン中のスタッフが自分のメールアドレスやパスワードを変更するため
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // スタッフ管理(管理者のみ使用可能)
    Route::middleware('can:admin')->group(function () {
        
        // 削除スタッフの一覧・復活
        Route::get('staffs/trashed', [StaffController::class, 'trashed'])->name('staffs.trashed');
        Route::patch('staffs/{id}/restore', [StaffController::class, 'restore'])->name('staffs.restore');
        Route::delete('staffs/{id}/force', [StaffController::class, 'forceDelete'])->name('staffs.force-delete');
        Route::resource('staffs', StaffController::class)->except(['show']);

        

        // 料金表の管理
        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');

        // スポンサー
        Route::resource('sponsors', SponsorController::class)->except(['show', 'edit', 'update']);
    });
});

// お客さん登録用（authグループの外（公開））
// URLのチェック
Route::get('/register/guest', [GuestRegistrationController::class, 'create'])->name('register.guest.form')->middleware('signed');

// 確認画面へ（confirm）
Route::post('/register/guest/confirm', [GuestRegistrationController::class, 'confirm'])->name('register.guest.confirm');

// 保存処理（store）
Route::post('/register/guest/store', [GuestRegistrationController::class, 'store'])->name('register.guest.store');

// 完了画面（complete）
Route::get('/register/guest/complete', [GuestRegistrationController::class, 'complete'])->name('register.guest.complete');

// セッション切れ案内（公開・保護なし）
Route::get('/register/guest/expired', [GuestRegistrationController::class, 'expired'])->name('register.guest.expired');

require __DIR__.'/auth.php';
