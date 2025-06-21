<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BacklinkController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Projects Routes
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/check-all-backlinks', [ProjectController::class, 'checkAllBacklinks'])->name('projects.check-all-backlinks');
    Route::delete('/projects/bulk-delete', [ProjectController::class, 'bulkDelete'])->name('projects.bulk-delete');
    Route::delete('/projects/{project}/bulk-delete-backlinks', [ProjectController::class, 'bulkDeleteBacklinks'])->name('projects.bulk-delete-backlinks');
    
    // Backlinks Routes
    Route::resource('backlinks', BacklinkController::class);
    Route::post('/backlinks/{backlink}/check', [BacklinkController::class, 'check'])->name('backlinks.check');
    Route::post('/backlinks/bulk-check', [BacklinkController::class, 'bulkCheck'])->name('backlinks.bulk-check');
    Route::delete('/backlinks/bulk-delete', [BacklinkController::class, 'bulkDelete'])->name('backlinks.bulk-delete');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
