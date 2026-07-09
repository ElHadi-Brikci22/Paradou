<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ClientApiController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TicketPrintController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

// Public Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Workspace Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}/print-ticket', [TicketPrintController::class, 'printTicket'])->name('orders.print-ticket');
    Route::get('/orders/{id}/print-tags', [TicketPrintController::class, 'printTags'])->name('orders.print-tags');
    Route::get('/orders/{id}/print-all', [TicketPrintController::class, 'printAll'])->name('orders.print-all');

    // Admin Only Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::resource('/admin/users', UserController::class)->names('admin.users')->except(['create', 'show', 'edit']);
    });

    Route::prefix('api')->group(function () {
        Route::get('/clients/search', [ClientApiController::class, 'search'])->name('api.clients.search');
        Route::post('/clients', [ClientApiController::class, 'store'])->name('api.clients.store');
        Route::post('/order-items/{id}/ready', [OrderManagementController::class, 'toggleItemReady'])->name('api.order-items.ready');
        Route::post('/orders/{id}/deliver', [OrderManagementController::class, 'deliver'])->name('api.orders.deliver');
    });

    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});
