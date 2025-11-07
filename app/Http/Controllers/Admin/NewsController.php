<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
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
     * Liste de toutes les actualités
     */
    public function index(Request $request)
    {
        $query = News::with(['user', 'category']);

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
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $news = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => News::count(),
            'published' => News::where('status', 'published')->count(),
            'draft' => News::where('status', 'draft')->count(),
            'archived' => News::where('status', 'archived')->count(),
        ];

        return view('admin.news.index', compact('news', 'stats'));
    }

    /**
     * Actualités en attente de validation
     */
    public function pending()
    {
        $news = News::with(['user', 'category'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.news.pending', compact('news'));
    }

    /**
     * Voir les détails d'une actualité
     */
    public function show($id)
    {
        $news = News::with(['user', 'category'])->findOrFail($id);
        return view('admin.news.show', compact('news'));
    }

    /**
     * Publier une actualité (approuver un brouillon)
     */
    public function publish($id)
    {
        $news = News::findOrFail($id);

        $news->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()->back()->with('success', "L'actualité \"{$news->title}\" a été publiée avec succès !");
    }

    /**
     * Archiver une actualité
     */
    public function archive($id)
    {
        $news = News::findOrFail($id);

        $news->update(['status' => 'archived']);

        return redirect()->back()->with('success', "L'actualité a été archivée.");
    }

    /**
     * Restaurer une actualité archivée
     */
    public function restore($id)
    {
        $news = News::findOrFail($id);

        $news->update(['status' => 'published']);

        return redirect()->back()->with('success', "L'actualité a été restaurée.");
    }

    /**
     * Basculer le statut "à la une"
     */
    public function toggleFeatured($id)
    {
        $news = News::findOrFail($id);

        $news->update(['is_featured' => !$news->is_featured]);

        $message = $news->is_featured
            ? "L'actualité a été mise à la une."
            : "L'actualité a été retirée de la une.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Supprimer une actualité
     */
    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $title = $news->title;

        $news->delete();

        return redirect()->route('admin.news.index')->with('success', "L'actualité \"{$title}\" a été supprimée avec succès.");
    }
}
