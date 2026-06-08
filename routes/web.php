<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogImageUploadController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NewsletterTrackingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\EditUser;
use App\Livewire\Admin\InviteUser;
use App\Livewire\Admin\ManageCategories;
use App\Livewire\Admin\ManagePosts as AdminManagePosts;
use App\Livewire\Admin\ManageVillages;
use App\Livewire\Admin\ModerateComments as AdminModerateComments;
use App\Livewire\Admin\ModerateProfiles as AdminModerateProfiles;
use App\Livewire\Admin\Newsletter\Campaigns;
use App\Livewire\Admin\Newsletter\CreateCampaign;
use App\Livewire\Admin\Newsletter\EditCampaign;
use App\Livewire\Admin\Newsletter\PreviewCampaign;
use App\Livewire\Admin\Newsletter\Subscribers;
use App\Livewire\Admin\RoleMatrix;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Users;
use App\Livewire\Account\CancelDeletion;
use App\Livewire\Account\ConfirmDeletion;
use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Blog\CreatePost;
use App\Livewire\Blog\EditPost;
use App\Livewire\Blog\MyPosts;
use App\Livewire\Blog\PreviewPost;
use App\Livewire\Directory\SearchDirectory;
use App\Livewire\Profile\CreateProfile;
use App\Livewire\Profile\EditProfile;
use App\Models\BlogPost;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignSend;
use App\Models\NewsletterSubscriber;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

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
        'members' => User::count(),
        'profiles' => Profile::verified()->count(),
        'countries' => Profile::verified()->distinct('country')->count('country'),
        'posts' => BlogPost::published()->count(),
    ];

    return view('welcome', compact('recentPosts', 'featuredProfiles', 'sectors', 'stats'));
})->name('home');

// SEO — sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Static content pages
Route::view('/qui-sommes-nous', 'pages.qui-sommes-nous')->name('pages.about-us');
Route::view('/a-propos-de-bassila', 'pages.a-propos-de-bassila')->name('pages.about-bassila');
Route::view('/cgu', 'pages.cgu')->name('pages.cgu');
Route::view('/politique-de-confidentialite', 'pages.politique-confidentialite')->name('pages.privacy');
Route::view('/mentions-legales', 'pages.mentions-legales')->name('pages.legal');

// Auth
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/inscription', Register::class)
        ->middleware('registration.check')
        ->name('register');
    Route::get('/connexion', Login::class)->name('login');
    Route::get('/mot-de-passe-oublie', ForgotPassword::class)->name('password.request');
    Route::get('/reinitialiser-mot-de-passe/{token}', ResetPassword::class)->name('password.reset');

    // Google OAuth (sign in / register)
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/deconnexion', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

// Public invitation acceptance (no auth required — token-gated)
Route::get('/invitation/{token}', AcceptInvitation::class)->name('invitation.accept');

// Account deletion confirmation (no auth required — token-gated)
Route::get('/compte/suppression/confirmer/{token}', ConfirmDeletion::class)
    ->middleware('throttle:6,1')
    ->name('account.deletion.confirm');

// Account deletion cancellation (auth required — grace period)
Route::get('/compte/suppression/annuler', CancelDeletion::class)
    ->middleware('auth')
    ->name('account.deletion.cancel');

// Email verification
Route::get('/email/verify', VerifyEmail::class)->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
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
    Route::get('/mes-articles', MyPosts::class)->name('blog.mine');
    Route::get('/blog/preview/{post}', PreviewPost::class)->name('blog.preview');
    Route::get('/blog/{slug}/modifier', EditPost::class)->name('blog.edit');

    Route::post('/blog/upload-image', [BlogImageUploadController::class, 'upload'])
        ->middleware('can:create,App\Models\BlogPost')
        ->name('blog.upload-image');
});

// Newsletter - public (no auth required)
Route::get('/newsletter/confirmer/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/desabonner/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
// RFC 8058 one-click unsubscribe (POST from mail clients — CSRF exempted in bootstrap/app.php)
Route::post('/newsletter/desabonner/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe.post');
// Open tracking pixel
Route::get('/newsletter/pixel/{token}.gif', [NewsletterTrackingController::class, 'pixel'])->name('newsletter.pixel');

// Blog - public
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Admin — auth + role:admin, Livewire pages
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');
    Route::get('/profils', AdminModerateProfiles::class)->name('profiles');
    Route::get('/articles', AdminManagePosts::class)->name('posts');
    Route::get('/categories', ManageCategories::class)->name('categories');
    Route::get('/villages', ManageVillages::class)->name('villages');
    Route::get('/commentaires', AdminModerateComments::class)->name('comments');
    Route::get('/utilisateurs', Users::class)->name('users');
    Route::get('/utilisateurs/inviter', InviteUser::class)->name('users.invite');
    Route::get('/utilisateurs/{user}/editer', EditUser::class)->name('users.edit');
    Route::get('/roles', RoleMatrix::class)->name('roles');
    Route::get('/suppressions', \App\Livewire\Admin\DeletionRequests::class)->name('deletions');
    Route::get('/parametres', Settings::class)->name('settings');
    Route::get('/newsletter', Campaigns::class)->name('newsletter');
    Route::get('/newsletter/creer', CreateCampaign::class)->name('newsletter.create');
    Route::get('/newsletter/{campaign}/editer', EditCampaign::class)->name('newsletter.edit');
    Route::get('/newsletter/{campaign}/apercu', PreviewCampaign::class)->name('newsletter.preview');
    Route::get('/newsletter/abonnes', Subscribers::class)->name('newsletter.subscribers');
    Route::get('/newsletter/{campaign}/html', function (NewsletterCampaign $campaign) {
        // Fake subscriber and send for preview rendering
        $sub = new NewsletterSubscriber(['email' => 'apercu@example.com', 'unsubscribe_token' => 'preview']);
        $send = new NewsletterCampaignSend(['open_token' => 'preview']);

        return view('mail.newsletter.newsletter', compact('campaign', 'sub', 'send'));
    })->name('newsletter.html');
});
