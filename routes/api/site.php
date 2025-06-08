<?php

use Application\Api\Address\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [AddressController::class, 'getCountries'])->name('get-countries');
Route::get('/provinces/{country}', [AddressController::class, 'getProvinces'])->name('get-provinces');
Route::get('/cities/{province}', [AddressController::class, 'getCities'])->name('get-cities');
