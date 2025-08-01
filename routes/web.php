<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OltController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SnmpController;

Route::get('/snmp-read', [SnmpController::class, 'read']);

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// OLT Management Routes
Route::middleware('auth')->prefix('olt')->name('olt.')->group(function () {
    Route::get('/', [OltController::class, 'index'])->name('index');
    Route::get('/available-cards', [OltController::class, 'getAvailableCards']); // Get available cards
    Route::get('/unconfigured-onus', [OltController::class, 'getUnconfiguredOnus']); // Get unconfigured ONUs
    Route::post('/configured-onus', [OltController::class, 'getConfiguredOnus']); // Get configured ONUs
    Route::post('/search-onu-by-serial', [OltController::class, 'searchOnuBySerial']); // Search ONU by Serial Number
    Route::post('/configure', [OltController::class, 'configureOnu']); // Configure ONU
    Route::post('/delete-onu', [OltController::class, 'deleteOnu']); // Delete ONU
    Route::post('/port-info-fast', [OltController::class, 'getPortInfoFast']); // Get port info (fast)
    Route::post('/refresh-vlan-profiles', [OltController::class, 'refreshVlanProfiles']); // Refresh VLAN profiles
    Route::get('/debug-raw', [OltController::class, 'debugRawOutput']); // Debug raw output
});

// User Management Routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserManagementController::class);
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
