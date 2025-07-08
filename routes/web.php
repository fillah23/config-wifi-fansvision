<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OltController;

Route::get('/', [OltController::class, 'index']);

// OLT Management Routes
Route::prefix('olt')->group(function () {
    Route::get('/unconfigured-onus', [OltController::class, 'getUnconfiguredOnus']);
    Route::post('/port-info', [OltController::class, 'getPortInfo']);
    Route::post('/port-info-fast', [OltController::class, 'getPortInfoFast']); // Fast port info
    Route::post('/configure', [OltController::class, 'configureOnu']);
    Route::get('/debug-raw', [OltController::class, 'debugRawOutput']); // Debug endpoint
    Route::post('/delete-onu', [OltController::class, 'deleteOnu']); // Delete ONU
    Route::post('/configured-onus', [OltController::class, 'getConfiguredOnus']); // Get configured ONUs
    Route::get('/available-cards', [OltController::class, 'getAvailableCards']); // Get available cards
    Route::post('/port-info-with-card', [OltController::class, 'getPortInfoWithCard']); // Get port info with specific card
    
    // SNMP VLAN Profile endpoints
    Route::get('/vlan-profiles', [OltController::class, 'getVlanProfiles']); // Get VLAN profiles via SNMP
    Route::post('/refresh-vlan-profiles', [OltController::class, 'refreshVlanProfiles']); // Refresh VLAN profiles
});
