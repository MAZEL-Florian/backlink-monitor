<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BacklinkController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Route de test pour vérifier la base de données
Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        $users = DB::table('users')->count();
        return "Connexion DB OK. Nombre d'utilisateurs: " . $users;
    } catch (\Exception $e) {
        return "Erreur DB: " . $e->getMessage();
    }
});

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

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Projects Routes
    Route::resource('projects', ProjectController::class);
    
    // Backlinks Routes
    Route::resource('backlinks', BacklinkController::class);
    Route::post('/backlinks/{backlink}/check', [BacklinkController::class, 'check'])->name('backlinks.check');
    Route::post('/backlinks/bulk-check', [BacklinkController::class, 'bulkCheck'])->name('backlinks.bulk-check');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
