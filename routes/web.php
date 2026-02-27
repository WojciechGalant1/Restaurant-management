<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\WaiterController;
use App\Http\Controllers\HostController;
use App\Http\Controllers\Manager\CancellationRequestController;
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
    Route::post('orders/{order}/bill', [BillController::class, 'store'])->name('orders.bill.store');
    Route::post('bills/{bill}/payments', [BillController::class, 'addPayment'])->name('bills.payments.store');
    Route::post('bills/{bill}/cancel', [BillController::class, 'cancel'])->name('bills.cancel');
    Route::resource('tables', TableController::class);
    Route::post('tables/{table}/assign', [TableController::class, 'assign'])->name('tables.assign');
    Route::post('tables/{table}/seat-walk-in', [TableController::class, 'seatWalkIn'])->name('tables.seat-walk-in');
    Route::post('tables/{table}/complete-cleaning', [TableController::class, 'completeCleaning'])->name('tables.complete-cleaning');
    Route::resource('rooms', RoomController::class);
    Route::post('reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('reservations/{reservation}/seat', [ReservationController::class, 'seat'])->name('reservations.seat');
    Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::resource('reservations', ReservationController::class);
    Route::resource('dishes', DishController::class);
    Route::resource('menu-items', MenuItemController::class);

    // JSON endpoints (same middleware as web, under /api prefix)
    Route::prefix('api')->group(function () {
        Route::get('shifts/calendar-events', [ShiftController::class, 'calendarEvents'])->name('shifts.calendar-events');
        Route::get('shifts/availability', [ShiftController::class, 'availability'])->name('shifts.availability');
        Route::get('shifts/coverage', [ShiftController::class, 'coverage'])->name('shifts.coverage');
        Route::get('reservations/calendar-events', [ReservationController::class, 'calendarEvents'])->name('reservations.calendar-events');
        Route::get('reservations/available-tables', [ReservationController::class, 'availableTables'])->name('reservations.available-tables');
        Route::get('reservations/customer-by-phone', [ReservationController::class, 'customerByPhone'])->name('reservations.customer-by-phone');
        Route::get('tables/floor-data', [TableController::class, 'floorData'])->name('tables.floor-data');
        Route::post('tables/reorder', [TableController::class, 'reorder'])->name('tables.reorder');
    });

    Route::resource('shifts', ShiftController::class);
    Route::post('shifts/{shift}/clock-in', [ShiftController::class, 'clockIn'])->name('shifts.clock-in');

    // Manager: cancellation requests
    Route::get('manager/cancellation-requests', [CancellationRequestController::class, 'index'])->name('manager.cancellation-requests.index');
    Route::post('manager/cancellation-requests/{cancellationRequest}/approve', [CancellationRequestController::class, 'approve'])->name('manager.cancellation-requests.approve');
    Route::post('manager/cancellation-requests/{cancellationRequest}/reject', [CancellationRequestController::class, 'reject'])->name('manager.cancellation-requests.reject');
    Route::resource('users', UserController::class);
    Route::resource('invoices', InvoiceController::class);

    // Kitchen
    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::patch('/kitchen/items/{orderItem}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.update-status');

    // Host — today's timeline (reservations by room)
    Route::get('/host/today', [HostController::class, 'today'])->name('host.today');

    // Waiter
    Route::get('/waiter', [WaiterController::class, 'index'])->name('waiter.index');
    Route::patch('/waiter/items/{orderItem}/mark-served', [WaiterController::class, 'markAsServed'])->name('waiter.mark-served');
    Route::patch('/waiter/reservations/{reservation}/mark-seated', [WaiterController::class, 'markReservationAsSeated'])->name('waiter.reservation.mark-seated');
});

require __DIR__.'/auth.php';
