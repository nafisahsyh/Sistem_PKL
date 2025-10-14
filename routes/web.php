<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

//route dashboard
 Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
 
 //route logout
 Route::post('/logout', [LoginController::class, 'logout'])->name('logout');