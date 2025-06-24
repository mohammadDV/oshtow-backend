<?php

use Application\Api\Address\Controllers\AddressController;
use Application\Api\Claim\Controllers\ClaimController;
use Application\Api\Payment\Controllers\PaymentController;
use Application\Api\Plan\Controllers\PlanController;
use Application\Api\Plan\Controllers\SubscribeController;
use Application\Api\Project\Controllers\ProjectCategoryController;
use Application\Api\Project\Controllers\ProjectController;
use Application\Api\Ticket\Controllers\TicketController;
use Application\Api\Ticket\Controllers\TicketSubjectController;
use Application\Api\User\Controllers\UserController;
use Application\Api\Wallet\Controllers\WalletController;
use Application\Api\Wallet\Controllers\WithdrawalTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [AddressController::class, 'activeCountries'])->name('active-countries');
Route::get('/provinces/{country}', [AddressController::class, 'activeProvinces'])->name('active-provinces');
Route::get('/cities/{province}', [AddressController::class, 'activeCities'])->name('active-cities');

// Plan
Route::get('/active-plans', [PlanController::class, 'activePlans'])->name('active-plans');
Route::get('/active-project-categories', [ProjectCategoryController::class, 'activeProjectCategories'])->name('active-project-categories');
Route::get('/active-projects', [ProjectController::class, 'activeProjects'])->name('active-projects');
Route::get('/active-subjects', [TicketSubjectController::class, 'activeSubjects'])->name('active-subjects');
Route::middleware(['auth:sanctum', 'auth', 'throttle:200,1'])->prefix('profile')->name('profile.')->group(function() {
    Route::resource('plans', PlanController::class);
    Route::resource('project-categories', ProjectCategoryController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('tickets', TicketController::class);
    Route::post('/ticket-status/{ticket}', [TicketController::class, 'changeStatus'])->name('profile.ticket.change-status');
    Route::resource('ticket-subjects', TicketSubjectController::class);


    // activity count
    Route::get('/activity-count', [UserController::class, 'getActivityCount'])->name('profile.activity.count');

    // subscribe
    Route::get('/subscriptions', [SubscribeController::class, 'index'])->name('profile.subscriptions');
    Route::get('/subscription-active', [SubscribeController::class, 'activeSubscription'])->name('profile.subscriptions.active');
    Route::get('/subscribe/{plan}', [SubscribeController::class, 'store'])->name('profile.subscribe.plan');

    // wallet
    Route::get('/wallets', [WalletController::class, 'index']);
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/top-up', [WalletController::class, 'topUp']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);

    // withdraw
    Route::post('/withdraws', [WithdrawalTransactionController::class, 'store']);
    Route::get('/withdraws', [WithdrawalTransactionController::class, 'index']);
    // just for admin
    Route::patch('/withdraws/{withdrawalTransaction}/status', [WithdrawalTransactionController::class, 'updateStatus']);

});

Route::middleware(['auth:sanctum', 'auth', 'throttle:200,1'])->group(function() {
    Route::post('/claims', [ClaimController::class, 'store'])->name('claim.store');
    Route::patch('/claims/{claim}', [ClaimController::class, 'update'])->name('claim.update');
    Route::get('/claims/project/{project}', [ClaimController::class, 'getClaimsPerProject'])->name('claim.index');
    Route::get('/claims/{claim}', [ClaimController::class, 'show'])->name('claim.show');
    Route::get('/claims/{claim}/approve', [ClaimController::class, 'approveClaim'])->name('claim.approve');
    Route::get('/claims/{claim}/paid', [ClaimController::class, 'paidClaim'])->name('claim.paid');
    Route::post('/claims/{claim}/inprogress', [ClaimController::class, 'inprogressClaim'])->name('claim.inprogress');
    Route::post('/claims/{claim}/delivered', [ClaimController::class, 'deliveredClaim'])->name('claim.delivered');
});

// Projects
Route::prefix('projects')->group(function () {
    Route::get('featured', [ProjectController::class, 'featured']);
    Route::get('search', [ProjectController::class, 'search']);
});
