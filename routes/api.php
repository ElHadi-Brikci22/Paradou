<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PosApiController;

/*
|--------------------------------------------------------------------------
| POS API Routes for Cloud <-> Desktop Synchronization
|--------------------------------------------------------------------------
*/

Route::prefix('pos')->group(function () {
    // 1. Health check & network ping
    Route::get('/ping', [PosApiController::class, 'ping'])->name('api.pos.ping');

    // 2. Full catalog & clients bootstrap for local cache
    Route::get('/bootstrap', [PosApiController::class, 'bootstrap'])->name('api.pos.bootstrap');

    // 3. Incremental updates pull
    Route::get('/sync/pull', [PosApiController::class, 'pullUpdates'])->name('api.pos.sync.pull');

    // 4. Batch push offline orders to cloud
    Route::post('/sync/orders', [PosApiController::class, 'syncOrders'])->name('api.pos.sync.orders');

    // 5. Batch push offline created clients to cloud
    Route::post('/sync/clients', [PosApiController::class, 'syncClients'])->name('api.pos.sync.clients');
});
