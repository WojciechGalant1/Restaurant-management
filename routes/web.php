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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\WaiterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard/revenue', [DashboardController::class, 'revenue'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.revenue');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Restaurant Management
    Route::resource('orders', OrderController::class);
    Route::resource('tables', TableController::class);
    Route::post('tables/{table}/assign', [TableController::class, 'assign'])->name('tables.assign');
    Route::resource('reservations', ReservationController::class);
    Route::resource('dishes', DishController::class);
    Route::resource('menu-items', MenuItemController::class);

    // JSON endpoints (same middleware as web, under /api prefix)
    Route::prefix('api')->group(function () {
        Route::get('shifts/calendar-events', [ShiftController::class, 'calendarEvents'])->name('shifts.calendar-events');
        Route::get('shifts/availability', [ShiftController::class, 'availability'])->name('shifts.availability');
        Route::get('shifts/coverage', [ShiftController::class, 'coverage'])->name('shifts.coverage');
        Route::get('reservations/calendar-events', [ReservationController::class, 'calendarEvents'])->name('reservations.calendar-events');
        Route::get('tables/floor-data', [TableController::class, 'floorData'])->name('tables.floor-data');
    });

    Route::resource('shifts', ShiftController::class);
    Route::resource('users', UserController::class);
    Route::resource('invoices', InvoiceController::class);

    // Kitchen
    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::patch('/kitchen/items/{orderItem}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.update-status');

    // Waiter
    Route::get('/waiter', [WaiterController::class, 'index'])->name('waiter.index');
    Route::patch('/waiter/items/{orderItem}/mark-served', [WaiterController::class, 'markAsServed'])->name('waiter.mark-served');
    Route::patch('/waiter/reservations/{reservation}/mark-seated', [WaiterController::class, 'markReservationAsSeated'])->name('waiter.reservation.mark-seated');
    Route::patch('/waiter/reservations/{reservation}/mark-no-show', [WaiterController::class, 'markReservationAsNoShow'])->name('waiter.reservation.mark-no-show');
    Route::patch('/waiter/reservations/{reservation}/status', [WaiterController::class, 'updateReservationStatus'])->name('waiter.reservation.update-status');
});

require __DIR__.'/auth.php';
