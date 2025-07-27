<?php

use Application\Api\Address\Controllers\AddressController;
use Application\Api\Chat\Controllers\ChatController;
use Application\Api\Claim\Controllers\ClaimController;
use Application\Api\File\Controllers\FileController;
use Application\Api\IdentityRecord\Controllers\IdentityRecordController;
use Application\Api\Notification\Controllers\NotificationController;
use Application\Api\Payment\Controllers\PaymentController;
use Application\Api\Plan\Controllers\PlanController;
use Application\Api\Plan\Controllers\SubscribeController;
use Application\Api\Post\Controllers\PostController;
use Application\Api\Project\Controllers\ProjectCategoryController;
use Application\Api\Project\Controllers\ProjectController;
use Application\Api\Review\Controllers\ReviewController;
use Application\Api\Ticket\Controllers\TicketController;
use Application\Api\Ticket\Controllers\TicketSubjectController;
use Application\Api\User\Controllers\AuthController;
use Application\Api\User\Controllers\UserController;
use Application\Api\Wallet\Controllers\WalletController;
use Application\Api\Wallet\Controllers\WalletTransactionController;
use Application\Api\Wallet\Controllers\WithdrawalTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [AddressController::class, 'activeCountries'])->name('active-countries');
Route::get('/provinces/{country}', [AddressController::class, 'activeProvinces'])->name('active-provinces');
Route::get('/cities/{province}', [AddressController::class, 'activeCities'])->name('active-cities');
Route::get('/cities/{city}/details', [AddressController::class, 'getCityDetails'])->name('city-details');
Route::get('/cities', [AddressController::class, 'getCitiesPaginate'])->name('city-search');

// Plan
Route::get('/active-plans', [PlanController::class, 'activePlans'])->name('active-plans');
Route::get('/active-project-categories', [ProjectCategoryController::class, 'activeProjectCategories'])->name('active-project-categories');
Route::get('/project/{project}', [ProjectController::class, 'show'])->name('project.show');
Route::get('/user-info/{user}', [UserController::class, 'getUserInfo'])->name('user.show');
Route::get('/active-subjects', [TicketSubjectController::class, 'activeSubjects'])->name('active-subjects');
Route::get('/user/{user}/reviews', [ReviewController::class, 'getReviewsPerUser']);

Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('user.payment.callback');
Route::get('/payment', [PaymentController::class, 'payment'])->name('user.payment');
Route::get('/payment/result/{id}', [PaymentController::class, 'show'])->name('user.payment.result');

Route::get('/posts', [PostController::class, 'getPosts'])->name('site.posts.index');
Route::get('/post/{post}', [PostController::class, 'getPostInfo'])->name('site.post.info');

// ->middleware(['auth', 'verified'])
Route::middleware(['auth:sanctum', 'auth', 'throttle:200,1'])->prefix('profile')->name('profile.')->group(function() {

    Route::get('/check-verification', [UserController::class, 'checkVerification'])->name('user.check.verification');

    // update user
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('user.update');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::patch('/users/{user}/change-password', [UserController::class, 'changePassword'])->name('user.change-password');

    // payment

    Route::resource('notifications', NotificationController::class);
    Route::get('/notifications-unread', [NotificationController::class, 'unread'])->name('unread-notifications');
    Route::get('/notifications-read-all', [NotificationController::class, 'readAll'])->name('read-all-notifications');

    Route::resource('plans', PlanController::class);
    Route::resource('identity-records', IdentityRecordController::class);
    Route::get('identity-records-info/{user}', [IdentityRecordController::class, 'getIdentityInfo']);
    Route::post('identity-records/{identityRecord}/change-status', [IdentityRecordController::class, 'changeStatus']);
    Route::resource('project-categories', ProjectCategoryController::class);
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('project.edit');
    Route::resource('tickets', TicketController::class);
    Route::post('/ticket-status/{ticket}', [TicketController::class, 'changeStatus'])->name('profile.ticket.change-status');
    Route::resource('ticket-subjects', TicketSubjectController::class);



    // activity count
    Route::get('/activity-count', [UserController::class, 'getActivityCount'])->name('profile.activity.count');
    Route::get('/dashboard-info', [UserController::class, 'getDashboardInfo'])->name('profile.dashboard.info');

    // subscribe
    Route::get('/subscriptions', [SubscribeController::class, 'index'])->name('profile.subscriptions');
    Route::get('/subscription-active', [SubscribeController::class, 'activeSubscription'])->name('profile.subscriptions.active');
    Route::post('/subscribe/{plan}', [SubscribeController::class, 'store'])->name('profile.subscribe.plan');

    // wallet
    Route::get('/wallets', [WalletController::class, 'index']);
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/top-up', [WalletController::class, 'topUp']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);

    Route::get('/wallet-transaction/{wallet}', [WalletTransactionController::class, 'index']);

    // withdraw
    Route::post('/withdraws', [WithdrawalTransactionController::class, 'store']);
    Route::get('/withdraws', [WithdrawalTransactionController::class, 'index']);

    // review
    Route::post('/reviews/{claim}', [ReviewController::class, 'store']);
    Route::get('/claim/{claim}/reviews', [ReviewController::class, 'getReviewsPerClaim']);
    // just for admin
    Route::patch('/reviews/{review}', [ReviewController::class, 'update']);
    Route::get('/reviews', [ReviewController::class, 'index']);

    // chats
    Route::prefix('chats')->group(function () {
        Route::post('/', [ChatController::class, 'indexPaginate'])->name('profile.chat.index');
        Route::get('/{chat}', [ChatController::class, 'show'])->name('profile.chat.show');
        Route::get('/info/{chat}', [ChatController::class, 'chatInfo'])->name('profile.chat.info');
        // Route::post('/{user}', [ChatController::class, 'store'])->name('profile.chat.store');
        Route::post('delete/{chat}', [ChatController::class, 'deleteMessages'])->name('profile.chat.delete.messages');
        // Route::post('/status/{chat}', [ChatController::class, 'changeStatus'])->name('profile.chat.change-status')->middleware('permission:chat_store');
    });







});


Route::middleware(['auth:sanctum', 'auth', 'check.admin'])->prefix('profile')->name('profile.')->group(function() {

    Route::patch('/withdraws/{withdrawalTransaction}/status', [WithdrawalTransactionController::class, 'updateStatus']);

    Route::post('/projects/{project}/reject', [ProjectController::class, 'reject'])->middleware('check.admin')->name('project.reject');
    Route::post('/projects/{project}/approve', [ProjectController::class, 'approve'])->middleware('check.admin')->name('project.approve');

    // Posts
    Route::prefix('posts')->group(function () {
        Route::get('/{post}', [PostController::class, 'show'])->name('profile.post.show');
        Route::get('/', [PostController::class, 'index'])->name('profile.post.index');
        Route::post('/', [PostController::class, 'store'])->name('profile.post.store');
        Route::patch('/{post}', [PostController::class, 'update'])->name('profile.post.update');
        Route::delete('/{post}', [PostController::class, 'destroy'])->name('profile.post.delete');
    });

});

// check with token
Route::get('/project/{project}/check-request', [ProjectController::class, 'checkRequestForClaim']);

Route::middleware(['auth:sanctum', 'auth', 'throttle:200,1'])->group(function() {


    Route::get('/mail', [AuthController::class, 'mail'])->name('mail.profile');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claim.store');
    Route::patch('/claims/{claim}', [ClaimController::class, 'update'])->name('claim.update');
    Route::get('/claims/project/{project}', [ClaimController::class, 'getClaimsPerProject'])->name('claim.project.index');
    Route::get('/claims/user/{user}', [ClaimController::class, 'getClaimsPerUser'])->name('claim.user.index');
    Route::get('/claims/{claim}', [ClaimController::class, 'show'])->name('claim.show');
    Route::get('/claims/{claim}/approve', [ClaimController::class, 'approveClaim'])->name('claim.approve');
    Route::post('/claims/{claim}/paid', [ClaimController::class, 'paidClaim'])->name('claim.paid');
    Route::post('/claims/{claim}/inprogress', [ClaimController::class, 'inprogressClaim'])->name('claim.inprogress');
    Route::post('/claims/{claim}/delivered', [ClaimController::class, 'deliveredClaim'])->name('claim.delivered');
    Route::get('/claims/{claim}/status', [ClaimController::class, 'getStatus'])->name('claim.status');
});

// upload files
Route::middleware(['auth:sanctum', 'auth', 'throttle:10,1'])->group(function() {
    Route::post('/upload-image', [FileController::class, 'uploadImage'])->name('site.upload-image');
    Route::post('/upload-video', [FileController::class, 'uploadVideo'])->name('site.upload-video');
    Route::post('/upload-file', [FileController::class, 'uploadFile'])->name('site.upload-file');
});

// Projects
Route::prefix('projects')->group(function () {
    Route::get('featured', [ProjectController::class, 'featured']);
    Route::get('search', [ProjectController::class, 'search']);
});
