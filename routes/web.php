<?php

use App\Domains\Appointments\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
