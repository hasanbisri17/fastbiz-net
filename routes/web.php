<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RouterOSController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// RouterOS Routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('routeros')->group(function () {
        Route::get('/system-resources', [RouterOSController::class, 'systemResources']);
        Route::get('/interfaces', [RouterOSController::class, 'interfaces']);
        Route::get('/hotspot/active-users', [RouterOSController::class, 'hotspotActiveUsers']);
        Route::post('/hotspot/users', [RouterOSController::class, 'addHotspotUser']);
    });

    // File Manager Routes
    Route::get('/file-manager/{router}/download/{file}', [App\Http\Controllers\FileManagerController::class, 'download'])
        ->name('filament.resources.file-manager.download');
    Route::delete('/file-manager/{router}/delete/{file}', [App\Http\Controllers\FileManagerController::class, 'delete'])
        ->name('filament.resources.file-manager.delete');

    // Invoice Routes
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoice.print');
});

require __DIR__.'/auth.php';
