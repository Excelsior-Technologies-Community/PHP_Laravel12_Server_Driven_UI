<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UIController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Home Page
Route::get('/', function () {
    return redirect()->route('demo', ['screen' => 'home']);
});

// Demo Pages
Route::get('/demo/{screen?}', [UIController::class, 'demo'])
    ->where('screen', 'home|profile|dashboard|settings')
    ->name('demo');

// =========================
// Admin Dashboard
// =========================

Route::get('/admin', [UIController::class, 'admin'])->name('admin');
Route::get(
    '/admin/export',
    [UIController::class, 'exportCSV']
)->name('component.export');

// =========================
// Component APIs
// =========================

// Get Components
Route::get('/api/ui/components/{screen}', [UIController::class, 'getComponents'])
    ->where('screen', 'home|profile|dashboard|settings')
    ->name('components');

// Get Available Screens
Route::get('/api/ui/screens', [UIController::class, 'getScreens'])
    ->name('screens');

// Create Component
Route::post(
    '/api/ui/components',
    [UIController::class, 'createComponent']
)->name('component.create');


// Toggle Status
Route::post(
    '/api/ui/components/{id}/toggle',
    [UIController::class, 'toggleComponent']
)->name('component.toggle');


// Delete Component
Route::delete(
    '/api/ui/components/{id}',
    [UIController::class, 'deleteComponent']
)->name('component.delete');
