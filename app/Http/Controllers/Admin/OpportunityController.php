<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpportunityController extends Controller
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
     * Liste de toutes les opportunités
     */
    public function index(Request $request)
    {
        $query = Opportunity::with(['user', 'category']);

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $opportunities = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Opportunity::count(),
            'active' => Opportunity::where('status', 'active')->count(),
            'pending' => Opportunity::where('status', 'pending')->count(),
            'closed' => Opportunity::where('status', 'closed')->count(),
        ];

        return view('admin.opportunities.index', compact('opportunities', 'stats'));
    }

    /**
     * Opportunités en attente de validation
     */
    public function pending()
    {
        $opportunities = Opportunity::with(['user', 'category'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.opportunities.pending', compact('opportunities'));
    }

    /**
     * Voir les détails d'une opportunité
     */
    public function show($id)
    {
        $opportunity = Opportunity::with(['user', 'category'])->findOrFail($id);
        return view('admin.opportunities.show', compact('opportunity'));
    }

    /**
     * Activer une opportunité (approuver)
     */
    public function activate($id)
    {
        $opportunity = Opportunity::findOrFail($id);

        $opportunity->update(['status' => 'active']);

        return redirect()->back()->with('success', "L'opportunité \"{$opportunity->title}\" a été approuvée et publiée !");
    }

    /**
     * Fermer une opportunité
     */
    public function close($id)
    {
        $opportunity = Opportunity::findOrFail($id);

        $opportunity->update(['status' => 'closed']);

        return redirect()->back()->with('success', "L'opportunité a été fermée.");
    }

    /**
     * Réouvrir une opportunité fermée
     */
    public function reopen($id)
    {
        $opportunity = Opportunity::findOrFail($id);

        $opportunity->update(['status' => 'active']);

        return redirect()->back()->with('success', "L'opportunité a été réouverte.");
    }

    /**
     * Basculer le statut "à la une"
     */
    public function toggleFeatured($id)
    {
        $opportunity = Opportunity::findOrFail($id);

        $opportunity->update(['is_featured' => !$opportunity->is_featured]);

        $message = $opportunity->is_featured
            ? "L'opportunité a été mise à la une."
            : "L'opportunité a été retirée de la une.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Supprimer une opportunité
     */
    public function destroy($id)
    {
        $opportunity = Opportunity::findOrFail($id);
        $title = $opportunity->title;

        $opportunity->delete();

        return redirect()->route('admin.opportunities.index')->with('success', "L'opportunité \"{$title}\" a été supprimée avec succès.");
    }
}
