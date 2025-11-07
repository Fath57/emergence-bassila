<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page d'accueil
Route::get('/', [LandingPageController::class, 'index'])->name('landing');

/*
|--------------------------------------------------------------------------
| Routes d'authentification
|--------------------------------------------------------------------------
*/

// Inscription
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Connexion
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Déconnexion
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Routes utilisateur (authentifié)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/dashboard/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::get('/dashboard/profile/edit', [DashboardController::class, 'editProfile'])->name('dashboard.profile.edit');
    Route::put('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
});

/*
|--------------------------------------------------------------------------
| Routes admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Dashboard admin
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // Gestion des utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/pending', [UserController::class, 'pending'])->name('users.pending');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

    // Actions sur les utilisateurs
    Route::post('/users/{id}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{id}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{id}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});
