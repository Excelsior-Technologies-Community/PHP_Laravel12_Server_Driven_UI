<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UIController;
use App\Http\Controllers\UIStudioController;
use App\Http\Controllers\UIExperimentController;
use App\Http\Controllers\UITelemetryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home Redirect
Route::get('/', function () {
    return redirect()->route('demo', ['screen' => 'home']);
});

// Demo Multi-Device & Dynamic UI Page
Route::get('/demo/{screen?}', [UIController::class, 'demo'])
    ->where('screen', 'home|profile|dashboard|settings')
    ->name('demo');

// ==========================================
// Admin Suites
// ==========================================

// 1. Component Management Console
Route::get('/admin', [UIController::class, 'admin'])->name('admin');
Route::get('/admin/export', [UIController::class, 'exportCSV'])->name('component.export');

// 2. Live Drag-and-Drop Canvas & Multi-Device Studio
Route::get('/admin/studio', [UIStudioController::class, 'index'])->name('admin.studio');

// 3. A/B Testing & Dynamic Experimentation Studio
Route::get('/admin/experiments', [UIExperimentController::class, 'index'])->name('admin.experiments');
Route::post('/admin/experiments', [UIExperimentController::class, 'store'])->name('admin.experiments.store');
Route::post('/admin/experiments/{id}/toggle', [UIExperimentController::class, 'toggle'])->name('admin.experiments.toggle');
Route::delete('/admin/experiments/{id}', [UIExperimentController::class, 'destroy'])->name('admin.experiments.destroy');

// 4. SDUI Analytics Radar & Component CTR Heatmap
Route::get('/admin/telemetry', [UITelemetryController::class, 'index'])->name('admin.telemetry');
Route::post('/admin/telemetry/synthetic', [UITelemetryController::class, 'generateSynthetic'])->name('admin.telemetry.synthetic');

// ==========================================
// Component & Telemetry APIs
// ==========================================

Route::prefix('api/ui')->group(function () {
    // Get Server-Driven UI Components (Platform & Version Adaptive + A/B Resolver)
    Route::get('/components/{screen}', [UIController::class, 'getComponents'])
        ->where('screen', 'home|profile|dashboard|settings')
        ->name('components');

    Route::get('/screens', [UIController::class, 'getScreens'])->name('screens');

    // Component CRUD & Reordering
    Route::post('/components', [UIController::class, 'createComponent'])->name('component.create');
    Route::post('/components/reorder', [UIStudioController::class, 'reorder'])->name('component.reorder');
    Route::put('/components/{id}', [UIStudioController::class, 'updateProperties'])->name('component.update');
    Route::post('/components/{id}/toggle', [UIController::class, 'toggleComponent'])->name('component.toggle');
    Route::delete('/components/{id}', [UIController::class, 'deleteComponent'])->name('component.delete');
    Route::get('/components/{id}/preview', [UIController::class, 'previewComponent'])->name('component.preview');
    Route::post('/components/{id}/duplicate', [UIController::class, 'duplicateComponent'])->name('component.duplicate');

    // Telemetry & CTR Tracker
    Route::post('/telemetry/impression', [UITelemetryController::class, 'logImpression'])->name('telemetry.impression');
    Route::post('/telemetry/click', [UITelemetryController::class, 'logClick'])->name('telemetry.click');
});
