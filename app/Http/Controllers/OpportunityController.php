<?php

namespace App\Http\Controllers;

use App\Opportunity;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OpportunityController extends Controller
{
    /**
     * Liste publique des opportunités
     */
    public function index(Request $request)
    {
        $query = Opportunity::with(['user', 'category'])
            ->where('status', 'active');

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par catégorie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtre par localisation
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filtre par télétravail
        if ($request->filled('remote_possible')) {
            $query->where('remote_possible', true);
        }

        // Filtre par type de contrat
        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        // Tri par date (plus récentes en premier)
        $opportunities = $query->orderBy('created_at', 'desc')->paginate(12);

        // Opportunités à la une
        $featured = Opportunity::with(['user', 'category'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Catégories pour le filtre
        $categories = Category::where('type', 'opportunity')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Statistiques
        $stats = [
            'total' => Opportunity::where('status', 'active')->count(),
            'emploi' => Opportunity::where('status', 'active')->where('type', 'Emploi')->count(),
            'stage' => Opportunity::where('status', 'active')->where('type', 'Stage')->count(),
            'collaboration' => Opportunity::where('status', 'active')->where('type', 'Collaboration')->count(),
        ];

        return view('opportunities.index', compact('opportunities', 'featured', 'categories', 'stats'));
    }

    /**
     * Afficher le détail d'une opportunité
     */
    public function show($id)
    {
        $opportunity = Opportunity::with(['user', 'category'])
            ->where('status', 'active')
            ->findOrFail($id);

        // Incrémenter le compteur de vues
        $opportunity->increment('views_count');

        // Opportunités similaires
        $similar = Opportunity::where('status', 'active')
            ->where('id', '!=', $opportunity->id)
            ->where(function ($query) use ($opportunity) {
                $query->where('category_id', $opportunity->category_id)
                    ->orWhere('type', $opportunity->type);
            })
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('opportunities.show', compact('opportunity', 'similar'));
    }

    /**
     * Formulaire de création d'opportunité
     */
    public function create()
    {
        $this->authorize('create', Opportunity::class);

        $categories = Category::where('type', 'opportunity')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('opportunities.create', compact('categories'));
    }

    /**
     * Enregistrer une nouvelle opportunité
     */
    public function store(Request $request)
    {
        $this->authorize('create', Opportunity::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Emploi,Stage,Bénévolat,Collaboration,Autre',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'location' => 'required|string|max:255',
            'remote_possible' => 'boolean',
            'company_name' => 'required|string|max:255',
            'company_website' => 'nullable|url',
            'contract_type' => 'nullable|in:CDI,CDD,Freelance,Stage,Bénévolat,Autre',
            'salary_range' => 'nullable|string|max:255',
            'experience_required' => 'nullable|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:50',
            'application_url' => 'nullable|url',
            'deadline' => 'nullable|date|after:today',
            'start_date' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
            'is_featured' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        // Statut selon le rôle
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'moderator') {
            $validated['status'] = 'active';
        } else {
            $validated['status'] = 'pending';
        }

        // Gestion is_featured (seulement pour admins)
        if (!isset($validated['is_featured']) || (Auth::user()->role !== 'admin' && Auth::user()->role !== 'moderator')) {
            $validated['is_featured'] = false;
        }

        $validated['remote_possible'] = $request->has('remote_possible');

        $opportunity = Opportunity::create($validated);

        $message = $validated['status'] === 'active'
            ? 'Opportunité publiée avec succès !'
            : 'Opportunité créée. Elle sera publiée après validation par un administrateur.';

        return redirect()->route('opportunities.show', $opportunity->id)->with('success', $message);
    }

    /**
     * Mes opportunités (dashboard)
     */
    public function myOpportunities()
    {
        $opportunities = Opportunity::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total' => Opportunity::where('user_id', Auth::id())->count(),
            'active' => Opportunity::where('user_id', Auth::id())->where('status', 'active')->count(),
            'pending' => Opportunity::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'views' => Opportunity::where('user_id', Auth::id())->sum('views_count'),
        ];

        return view('opportunities.my-opportunities', compact('opportunities', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $opportunity = Opportunity::findOrFail($id);
        $this->authorize('update', $opportunity);

        $categories = Category::where('type', 'opportunity')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('opportunities.edit', compact('opportunity', 'categories'));
    }

    /**
     * Mettre à jour une opportunité
     */
    public function update(Request $request, $id)
    {
        $opportunity = Opportunity::findOrFail($id);
        $this->authorize('update', $opportunity);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Emploi,Stage,Bénévolat,Collaboration,Autre',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'location' => 'required|string|max:255',
            'remote_possible' => 'boolean',
            'company_name' => 'required|string|max:255',
            'company_website' => 'nullable|url',
            'contract_type' => 'nullable|in:CDI,CDD,Freelance,Stage,Bénévolat,Autre',
            'salary_range' => 'nullable|string|max:255',
            'experience_required' => 'nullable|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:50',
            'application_url' => 'nullable|url',
            'deadline' => 'nullable|date',
            'start_date' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
            'is_featured' => 'boolean',
        ]);

        // Gestion is_featured (seulement pour admins)
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'moderator') {
            unset($validated['is_featured']);
        }

        $validated['remote_possible'] = $request->has('remote_possible');

        $opportunity->update($validated);

        return redirect()->route('opportunities.show', $opportunity->id)
            ->with('success', 'Opportunité mise à jour avec succès !');
    }

    /**
     * Supprimer une opportunité
     */
    public function destroy($id)
    {
        $opportunity = Opportunity::findOrFail($id);
        $this->authorize('delete', $opportunity);

        $opportunity->delete();

        return redirect()->route('opportunities.my')
            ->with('success', 'Opportunité supprimée avec succès !');
    }
}
