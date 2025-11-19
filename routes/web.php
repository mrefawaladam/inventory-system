<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// Authentication Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout (Authenticated only)
Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', function () {
            return view('chat.index');
        })->name('index');
    });

    // User Management Routes
    Route::resource('users', UserController::class);
    Route::post('users/{user}/assign-permission', [UserController::class, 'assignPermission'])->name('users.assign-permission');
    Route::delete('users/{user}/permissions/{permission}', [UserController::class, 'removePermission'])->name('users.remove-permission');

    // Warehouse Management Routes
    Route::resource('warehouses', WarehouseController::class);

    // Location Management Routes
    Route::resource('locations', LocationController::class);
    Route::get('locations/get-by-warehouse', [LocationController::class, 'getByWarehouse'])->name('locations.get-by-warehouse');

    // Item Management Routes
    Route::resource('items', ItemController::class);
    Route::get('items/{item}/barcode', [ItemController::class, 'barcode'])->name('items.barcode');

    // Stock Management Routes
    Route::resource('stocks', StockController::class);
    Route::post('stocks/increase', [StockController::class, 'increase'])->name('stocks.increase');
    Route::post('stocks/decrease', [StockController::class, 'decrease'])->name('stocks.decrease');
    Route::post('stocks/transfer', [StockController::class, 'transfer'])->name('stocks.transfer');

    // Transaction Routes - Inbound
    Route::prefix('inbound')->name('inbound.')->group(function () {
        Route::get('get-item-by-barcode', [InboundController::class, 'getItemByBarcode'])->name('getItemByBarcode');
        Route::get('get-locations-by-warehouse', [InboundController::class, 'getLocationsByWarehouse'])->name('getLocationsByWarehouse');
    });
    Route::resource('inbound', InboundController::class)->except(['show', 'edit', 'update', 'destroy']);

    // Transaction Routes - Outbound
    Route::prefix('outbound')->name('outbound.')->group(function () {
        Route::get('get-item-by-barcode', [OutboundController::class, 'getItemByBarcode'])->name('getItemByBarcode');
        Route::get('get-locations-by-warehouse', [OutboundController::class, 'getLocationsByWarehouse'])->name('getLocationsByWarehouse');
        Route::get('search-items', [OutboundController::class, 'searchItems'])->name('searchItems');
    });
    Route::resource('outbound', OutboundController::class)->except(['show', 'edit', 'update', 'destroy']);

    // Transaction Routes - Transfer
    Route::prefix('transfer')->name('transfer.')->group(function () {
        Route::get('get-item-by-barcode', [TransferController::class, 'getItemByBarcode'])->name('getItemByBarcode');
        Route::get('get-location-by-code', [TransferController::class, 'getLocationByCode'])->name('getLocationByCode');
        Route::get('get-locations-by-warehouse', [TransferController::class, 'getLocationsByWarehouse'])->name('getLocationsByWarehouse');
    });
    Route::resource('transfer', TransferController::class)->except(['show', 'edit', 'update', 'destroy']);

    // Reports Routes
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [ReportsController::class, 'exportExcel'])->name('reports.exportExcel');
    Route::get('reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.exportPdf');

    // Heatmap Analytics Routes
    Route::prefix('heatmap')->name('heatmap.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HeatmapController::class, 'index'])->name('index');
        Route::get('item-movement', [\App\Http\Controllers\HeatmapController::class, 'getItemMovementHeatmap'])->name('itemMovement');
        Route::get('warehouse-activity', [\App\Http\Controllers\HeatmapController::class, 'getWarehouseActivityHeatmap'])->name('warehouseActivity');
        Route::get('traffic', [\App\Http\Controllers\HeatmapController::class, 'getTrafficVisualization'])->name('traffic');
        Route::get('time-based', [\App\Http\Controllers\HeatmapController::class, 'getTimeBasedActivity'])->name('timeBased');
    });

    // Tracking Routes
    Route::prefix('tracking')->name('tracking.')->group(function () {
        Route::get('/', [TrackingController::class, 'index'])->name('index');
        Route::get('/routes', [TrackingController::class, 'getRoutes'])->name('routes');
        Route::get('/item-history/{itemId?}', [TrackingController::class, 'itemHistory'])->name('item-history');
        Route::get('/item-history/{itemId}/data', [TrackingController::class, 'getItemHistory'])->name('item-history.data');
    });

    // Blank Pages
    Route::get('/blank', function () {
        return view('pages.blank-page');
    })->name('blank');

    Route::get('/blank-page', function () {
        return view('pages.blank');
    })->name('blank.page');

    Route::get('/blank-simple', function () {
        return view('pages.blank-simple');
    })->name('blank.simple');
});
