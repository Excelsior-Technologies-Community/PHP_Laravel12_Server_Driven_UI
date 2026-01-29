<?php

use App\Http\Controllers\UIController;
use Illuminate\Support\Facades\Route;

// Set demo as the homepage
Route::get('/', function () {
    // Redirect to demo page with default screen (home)
    return redirect()->route('demo', ['screen' => 'home']);
});

// Or directly map to the demo controller
Route::get('/', [UIController::class, 'demo'])->name('home');
Route::get('/demo/{screen?}', [UIController::class, 'demo'])->where('screen', 'home|profile|dashboard|settings')->name('demo');

// Admin page
Route::get('/admin', [UIController::class, 'admin'])->name('admin');

// API routes
Route::get('/api/ui/components/{screen}', [UIController::class, 'getComponents'])->where('screen', 'home|profile|dashboard|settings');
Route::get('/api/ui/screens', [UIController::class, 'getScreens']);
Route::post('/api/ui/components', [UIController::class, 'createComponent']);
Route::post('/api/ui/components/{id}/toggle', [UIController::class, 'toggleComponent']);