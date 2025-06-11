<?php

use Application\Api\Address\Controllers\AddressController;
use Application\Api\Plan\Controllers\PlanController;
use Application\Api\Project\Controllers\ProjectCategoryController;
use Application\Api\Project\Controllers\ProjectController;
use Application\Api\Ticket\Controllers\TicketController;
use Application\Api\Ticket\Controllers\TicketSubjectController;
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
    Route::post('/ticket-status/{ticket}', [TicketController::class, 'changeStatus'])->name('profile.ticket.change-status')->middleware('permission:ticket_store');
    Route::resource('ticket-subjects', TicketSubjectController::class);
});

 // tickets
//  Route::prefix('tickets')->group(function () {
//     Route::get('/', [TicketController::class, 'indexPaginate'])->name('profile.ticket.index')->middleware('permission:ticket_show');
//     Route::get('/{ticket}', [TicketController::class, 'show'])->name('profile.ticket.show')->middleware('permission:ticket_show');
//     Route::post('/', [TicketController::class, 'store'])->name('profile.ticket.store')->middleware('permission:ticket_store');
//     Route::post('/{ticket}', [TicketController::class, 'storeMessage'])->name('profile.ticket.store.message')->middleware('permission:ticket_store');
//     Route::post('/status/{ticket}', [TicketController::class, 'changeStatus'])->name('profile.ticket.change-status')->middleware('permission:ticket_store');
// });

// ticket subjects
// Route::prefix('ticket-subjects')->group(function () {
//     Route::get('/', [TicketSubjectController::class, 'indexPaginate'])->name('profile.ticket-subject.index')->middleware('permission:subject_show');
//     Route::get('/{ticketSubject}', [TicketSubjectController::class, 'show'])->name('profile.ticket-subject.show')->middleware('permission:subject_show');
//     Route::post('/', [TicketSubjectController::class, 'store'])->name('profile.ticket-subject.store')->middleware('permission:subject_store');
//     Route::post('/{ticketSubject}', [TicketSubjectController::class, 'update'])->name('profile.ticket-subject.update')->middleware('permission:subject_update');
//     Route::delete('/{ticketSubject}', [TicketSubjectController::class, 'destroy'])->name('profile.ticket-subject.delete')->middleware('permission:subject_delete');
// });


