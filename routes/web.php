<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Restaurant Management
    Route::resource('orders', OrderController::class);
    Route::resource('tables', TableController::class);
    Route::resource('reservations', ReservationController::class);
    Route::resource('dishes', DishController::class);
    Route::resource('menu-items', MenuItemController::class);
    Route::resource('shifts', ShiftController::class);
    Route::resource('users', UserController::class);
    Route::resource('invoices', InvoiceController::class);
});

require __DIR__.'/auth.php';
