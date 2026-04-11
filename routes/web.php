<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlogController;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\ManagePosts as AdminManagePosts;
use App\Livewire\Admin\ModerateComments as AdminModerateComments;
use App\Livewire\Admin\ModerateProfiles as AdminModerateProfiles;
use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Profile\CreateProfile;
use App\Livewire\Profile\EditProfile;
use App\Livewire\Directory\SearchDirectory;
use App\Livewire\Blog\CreatePost;
use App\Livewire\Blog\EditPost;
use App\Models\BlogPost;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;

// Home
Route::get('/', function () {
    $recentPosts = BlogPost::published()
        ->with(['user', 'category'])
        ->latest('published_at')
        ->take(3)
        ->get();

    $featuredProfiles = Profile::verified()
        ->with('sector')
        ->latest('verified_at')
        ->take(6)
        ->get();

    $sectors = Sector::withCount(['profiles' => fn ($q) => $q->where('is_verified', true)])
        ->get()
        ->filter(fn ($s) => $s->profiles_count > 0)
        ->sortByDesc('profiles_count')
        ->take(8)
        ->values();

    $stats = [
        'members'   => User::count(),
        'profiles'  => Profile::verified()->count(),
        'countries' => Profile::verified()->distinct('country')->count('country'),
        'posts'     => BlogPost::published()->count(),
    ];

    return view('welcome', compact('recentPosts', 'featuredProfiles', 'sectors', 'stats'));
})->name('home');

// Auth
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/inscription', Register::class)
        ->middleware('registration.check')
        ->name('register');
    Route::get('/connexion', Login::class)->name('login');
    Route::get('/mot-de-passe-oublie', ForgotPassword::class)->name('password.request');
    Route::get('/reinitialiser-mot-de-passe/{token}', ResetPassword::class)->name('password.reset');
});

Route::post('/deconnexion', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

// Public invitation acceptance (no auth required — token-gated)
Route::get('/invitation/{token}', AcceptInvitation::class)->name('invitation.accept');

// Email verification
Route::get('/email/verify', VerifyEmail::class)->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('profile.create');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Directory (annuaire) - public
Route::get('/annuaire', SearchDirectory::class)->name('directory.index');

// Profiles - public
Route::get('/profils/{profile}', [ProfileController::class, 'show'])->name('profile.show');

// Profile management - auth + verified
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profil/creer', CreateProfile::class)->name('profile.create');
    Route::get('/profil/modifier', EditProfile::class)->name('profile.edit');
});

// Contact form is embedded as a Livewire component in profile/show.blade.php

// Blog management - auth + verified (must be before /blog/{slug} to avoid slug capture)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/blog/rediger', CreatePost::class)
        ->middleware('can:create,App\Models\BlogPost')
        ->name('blog.create');
    Route::get('/blog/{slug}/modifier', EditPost::class)->name('blog.edit');
});

// Blog - public
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Admin — auth + role:admin, Livewire pages
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',              AdminDashboard::class)->name('dashboard');
    Route::get('/profils',       AdminModerateProfiles::class)->name('profiles');
    Route::get('/articles',      AdminManagePosts::class)->name('posts');
    Route::get('/commentaires',  AdminModerateComments::class)->name('comments');
    Route::get('/parametres',    \App\Livewire\Admin\Settings::class)->name('settings');
});
