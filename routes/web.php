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
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\RubricsController;
use App\Http\Controllers\Admin\PriceController;

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
        
        // Price Management (wholesale & retail)
        Route::get('/admin/prices', [PriceController::class, 'index'])->name('admin.prices.index');
        Route::post('/admin/prices/update', [PriceController::class, 'update'])->name('admin.prices.update');

        // Catalog & pricing management
        Route::get('/admin/catalog', [CatalogController::class, 'index'])->name('admin.catalog.index');
        Route::post('/admin/catalog/item', [CatalogController::class, 'storeGarmentItem'])->name('admin.catalog.item.store');
        Route::put('/admin/catalog/item/{id}', [CatalogController::class, 'updateGarmentItem'])->name('admin.catalog.item.update');
        Route::delete('/admin/catalog/item/{id}', [CatalogController::class, 'destroyGarmentItem'])->name('admin.catalog.item.destroy');

        // Targets CRUD
        Route::post('/admin/catalog/target', [CatalogController::class, 'storeGarmentTarget'])->name('admin.catalog.target.store');
        Route::put('/admin/catalog/target/{id}', [CatalogController::class, 'updateGarmentTarget'])->name('admin.catalog.target.update');
        Route::delete('/admin/catalog/target/{id}', [CatalogController::class, 'destroyGarmentTarget'])->name('admin.catalog.target.destroy');

        // Services CRUD
        Route::post('/admin/catalog/service', [CatalogController::class, 'storeService'])->name('admin.catalog.service.store');
        Route::put('/admin/catalog/service/{id}', [CatalogController::class, 'updateService'])->name('admin.catalog.service.update');
        Route::delete('/admin/catalog/service/{id}', [CatalogController::class, 'destroyService'])->name('admin.catalog.service.destroy');

        // Flat file dictionaries & patterns management
        Route::get('/admin/rubrics', [RubricsController::class, 'index'])->name('admin.rubrics.index');
        Route::post('/admin/rubrics/save', [RubricsController::class, 'saveRubric'])->name('admin.rubrics.save');
    });

    Route::prefix('api')->group(function () {
        Route::get('/clients/search', [ClientApiController::class, 'search'])->name('api.clients.search');
        Route::post('/clients', [ClientApiController::class, 'store'])->name('api.clients.store');
        Route::post('/order-items/{id}/ready', [OrderManagementController::class, 'toggleItemReady'])->name('api.order-items.ready');
        Route::post('/orders/{id}/deliver', [OrderManagementController::class, 'deliver'])->name('api.orders.deliver');
    });

    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});
