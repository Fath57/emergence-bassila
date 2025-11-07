<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OpportunityController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page d'accueil
Route::get('/', [LandingPageController::class, 'index'])->name('landing');

/*
|--------------------------------------------------------------------------
| Routes publiques - Annuaire et membres
|--------------------------------------------------------------------------
*/

// Annuaire des membres
Route::get('/members', [MemberController::class, 'index'])->name('members.index');

// Recherche avancée
Route::get('/members/search', [MemberController::class, 'search'])->name('members.search');

// Profil public d'un membre
Route::get('/members/{id}', [MemberController::class, 'show'])->name('members.show');

/*
|--------------------------------------------------------------------------
| Routes publiques - Actualités et Événements
|--------------------------------------------------------------------------
*/

// Liste des actualités
Route::get('/news', [NewsController::class, 'index'])->name('news.index');

// Détail d'une actualité
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

/*
|--------------------------------------------------------------------------
| Routes publiques - Opportunités
|--------------------------------------------------------------------------
*/

// Liste des opportunités
Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');

// Détail d'une opportunité
Route::get('/opportunities/{id}', [OpportunityController::class, 'show'])->name('opportunities.show');

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

    // Actualités (gestion personnelle)
    Route::get('/news/my', [NewsController::class, 'myNews'])->name('news.my');
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');

    // Opportunités (gestion personnelle)
    Route::get('/opportunities/my', [OpportunityController::class, 'myOpportunities'])->name('opportunities.my');
    Route::get('/opportunities/create', [OpportunityController::class, 'create'])->name('opportunities.create');
    Route::post('/opportunities', [OpportunityController::class, 'store'])->name('opportunities.store');
    Route::get('/opportunities/{id}/edit', [OpportunityController::class, 'edit'])->name('opportunities.edit');
    Route::put('/opportunities/{id}', [OpportunityController::class, 'update'])->name('opportunities.update');
    Route::delete('/opportunities/{id}', [OpportunityController::class, 'destroy'])->name('opportunities.destroy');
});

/*
|--------------------------------------------------------------------------
| Routes admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
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

    // Gestion des actualités
    Route::get('/news', [\App\Http\Controllers\Admin\NewsController::class, 'index'])->name('news.index');
    Route::get('/news/pending', [\App\Http\Controllers\Admin\NewsController::class, 'pending'])->name('news.pending');
    Route::get('/news/{id}', [\App\Http\Controllers\Admin\NewsController::class, 'show'])->name('news.show');
    Route::post('/news/{id}/publish', [\App\Http\Controllers\Admin\NewsController::class, 'publish'])->name('news.publish');
    Route::post('/news/{id}/archive', [\App\Http\Controllers\Admin\NewsController::class, 'archive'])->name('news.archive');
    Route::post('/news/{id}/restore', [\App\Http\Controllers\Admin\NewsController::class, 'restore'])->name('news.restore');
    Route::post('/news/{id}/toggle-featured', [\App\Http\Controllers\Admin\NewsController::class, 'toggleFeatured'])->name('news.toggle-featured');
    Route::delete('/news/{id}', [\App\Http\Controllers\Admin\NewsController::class, 'destroy'])->name('news.destroy');

    // Gestion des opportunités
    Route::get('/opportunities', [\App\Http\Controllers\Admin\OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/pending', [\App\Http\Controllers\Admin\OpportunityController::class, 'pending'])->name('opportunities.pending');
    Route::get('/opportunities/{id}', [\App\Http\Controllers\Admin\OpportunityController::class, 'show'])->name('opportunities.show');
    Route::post('/opportunities/{id}/activate', [\App\Http\Controllers\Admin\OpportunityController::class, 'activate'])->name('opportunities.activate');
    Route::post('/opportunities/{id}/close', [\App\Http\Controllers\Admin\OpportunityController::class, 'close'])->name('opportunities.close');
    Route::post('/opportunities/{id}/reopen', [\App\Http\Controllers\Admin\OpportunityController::class, 'reopen'])->name('opportunities.reopen');
    Route::post('/opportunities/{id}/toggle-featured', [\App\Http\Controllers\Admin\OpportunityController::class, 'toggleFeatured'])->name('opportunities.toggle-featured');
    Route::delete('/opportunities/{id}', [\App\Http\Controllers\Admin\OpportunityController::class, 'destroy'])->name('opportunities.destroy');
});
