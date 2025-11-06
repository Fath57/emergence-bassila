<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', [LandingPageController::class, 'index'])->name('landing');

// Annuaire public (visible par tous)
Route::get('/annuaire', [DirectoryController::class, 'index'])->name('directory.index');
Route::get('/annuaire/{user}', [DirectoryController::class, 'show'])->name('directory.show');

// Dashboard - accessible uniquement aux utilisateurs approuvés
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', App\Http\Middleware\EnsureUserIsApproved::class])->name('dashboard');

// Routes profil utilisateur
Route::middleware(['auth', App\Http\Middleware\EnsureUserIsApproved::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes Admin - accessible uniquement aux administrateurs
Route::middleware(['auth', App\Http\Middleware\EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/reject', [AdminUserController::class, 'reject'])->name('users.reject');
        Route::post('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggleAdmin');
        Route::post('/gallery/{gallery}/approve', [AdminUserController::class, 'approveGallery'])->name('gallery.approve');
        Route::post('/gallery/{gallery}/reject', [AdminUserController::class, 'rejectGallery'])->name('gallery.reject');
    });

// Include Breeze auth routes
require __DIR__.'/auth.php';
