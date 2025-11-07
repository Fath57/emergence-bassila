<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Créer une nouvelle instance du contrôleur
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'moderator') {
                abort(403, 'Accès non autorisé.');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard admin
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),

            // Stats actualités
            'total_news' => \App\News::count(),
            'pending_news' => \App\News::where('status', 'draft')->count(),
            'published_news' => \App\News::where('status', 'published')->count(),

            // Stats opportunités
            'total_opportunities' => \App\Opportunity::count(),
            'pending_opportunities' => \App\Opportunity::where('status', 'pending')->count(),
            'active_opportunities' => \App\Opportunity::where('status', 'active')->count(),
        ];

        $pendingUsers = User::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pendingNews = \App\News::where('status', 'draft')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pendingOpportunities = \App\Opportunity::where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'pendingUsers', 'pendingNews', 'pendingOpportunities'));
    }

    /**
     * Liste de tous les utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtrer par statut
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Afficher les utilisateurs en attente
     */
    public function pending()
    {
        $users = User::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.pending', compact('users'));
    }

    /**
     * Voir les détails d'un utilisateur
     */
    public function show($id)
    {
        $user = User::with(['skills', 'experiences', 'educations'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Approuver un utilisateur
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Le compte de {$user->full_name} a été approuvé avec succès !");
    }

    /**
     * Suspendre un utilisateur
     */
    public function suspend($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Vous ne pouvez pas suspendre un administrateur.');
        }

        $user->update(['status' => 'suspended']);

        return redirect()->back()->with('success', "Le compte de {$user->full_name} a été suspendu.");
    }

    /**
     * Réactiver un utilisateur suspendu
     */
    public function reactivate($id)
    {
        $user = User::findOrFail($id);

        $user->update(['status' => 'active']);

        return redirect()->back()->with('success', "Le compte de {$user->full_name} a été réactivé.");
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Vous ne pouvez pas supprimer un administrateur.');
        }

        $user->delete();

        return redirect()->back()->with('success', "L'utilisateur a été supprimé avec succès.");
    }
}
