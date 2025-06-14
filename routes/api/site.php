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

// Projects
Route::prefix('projects')->group(function () {
    Route::get('featured', [ProjectController::class, 'featured']);
    Route::get('search', [ProjectController::class, 'search']);
});