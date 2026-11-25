<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    $role = Auth::user()->role;
    switch ($role) {
        case 'owner':
            return redirect()->route('reports.index');
        case 'kasir':
            return redirect()->route('pos.index');
        case 'gudang':
            return redirect()->route('inventory.index');
        case 'pembeli':
            return redirect()->route('portal.index');
        default:
            return redirect()->route('pos.index');
    }
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/switch-role', [AuthController::class, 'switchRole'])->middleware('role:owner')->name('switch.role');

    // POS Kasir & Transaksi (Owner & Kasir)
    Route::middleware('role:owner,kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/api/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/api/transactions/{id}', [TransactionController::class, 'detail'])->name('transactions.detail');

        Route::get('/receivables', [ReceivableController::class, 'index'])->name('receivables.index');
        Route::post('/api/receivables/pay', [ReceivableController::class, 'pay'])->name('receivables.pay');

        // Pendaftaran Pelanggan Baru oleh Kasir (Khusus Pembeli, Limit Kasbon 0)
        Route::post('/api/customers/register', [UserController::class, 'registerCustomer'])->name('customers.register');
    });

    // Gudang & Inventaris (Owner & Gudang)
    Route::middleware('role:owner,gudang')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/api/inventory/product', [InventoryController::class, 'storeProduct'])->name('inventory.storeProduct');
        Route::post('/api/inventory/category', [InventoryController::class, 'storeCategory'])->name('inventory.storeCategory');
        Route::post('/api/inventory/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventory.adjustStock');
        Route::post('/api/inventory/set-physical-stock', [InventoryController::class, 'setPhysicalStock'])->name('inventory.setPhysicalStock');
        Route::get('/api/inventory/{id}/history', [InventoryController::class, 'stockHistory'])->name('inventory.stockHistory');
    });

    // Laporan & Pengaturan Toko (Owner Only)
    Route::middleware('role:owner')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.exportCsv');
        Route::post('/api/transactions/void', [TransactionController::class, 'void'])->name('transactions.void');

        // Manajemen Akun & Hak Akses (Eksklusif Owner)
        Route::get('/roles', [UserController::class, 'index'])->name('roles.index');
        Route::post('/api/roles', [UserController::class, 'store'])->name('roles.store');
        Route::post('/api/roles/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('roles.toggleActive');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Riwayat Cicilan Piutang (Owner, Kasir, Pembeli)
    Route::get('/api/receivables/{id}/history', [ReceivableController::class, 'history'])
        ->middleware('role:owner,kasir,pembeli')
        ->name('receivables.history');

    // Portal Pelanggan Grosir (Pembeli & Owner)
    Route::middleware('role:owner,pembeli')->group(function () {
        Route::get('/portal', [PortalController::class, 'index'])->name('portal.index');
    });
});
