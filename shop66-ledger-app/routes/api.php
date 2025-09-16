<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentIngestionController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\StoreUserController;
use App\Http\Controllers\Api\TaxRegionController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tax-regions', TaxRegionController::class);
    Route::apiResource('stores', StoreController::class);

    Route::get('stores/{store}/dashboard', [DashboardController::class, 'index'])->name('stores.dashboard');
    
    // Reports
    Route::prefix('stores/{store}/reports')->name('stores.reports.')->group(function () {
        Route::get('financial-summary', [ReportsController::class, 'financialSummary'])->name('financial-summary');
        Route::get('vendor', [ReportsController::class, 'vendorReport'])->name('vendor');
        Route::get('category', [ReportsController::class, 'categoryReport'])->name('category');
        Route::post('export', [ReportsController::class, 'export'])->name('export');
        Route::get('download/{filename}', [ReportsController::class, 'download'])->name('download');
    });
    
    Route::apiResource('stores.users', StoreUserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('stores.categories', CategoryController::class);
    Route::apiResource('stores.vendors', VendorController::class);
    Route::apiResource('stores.customers', CustomerController::class);
    Route::apiResource('stores.items', ItemController::class);
    Route::apiResource('stores.accounts', AccountController::class);
    Route::apiResource('stores.budgets', BudgetController::class);
    Route::apiResource('stores.transactions', TransactionController::class);
    Route::apiResource('stores.documents', DocumentController::class);
    Route::apiResource('stores.documents.ingestions', DocumentIngestionController::class)->only(['index', 'show', 'store']);
    Route::apiResource('stores.documents.attachments', AttachmentController::class)->only(['index', 'store', 'destroy']);
});
