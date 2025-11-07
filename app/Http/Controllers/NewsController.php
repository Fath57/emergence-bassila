<?php

namespace App\Http\Controllers;

use App\News;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * Afficher toutes les actualités publiées
     */
    public function index(Request $request)
    {
        $query = News::with(['user', 'category'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc');

        // Filtre par type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $news = $query->paginate(12);

        // Actualités à la une
        $featured = News::where('status', 'published')
            ->where('is_featured', true)
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        $categories = Category::where('type', 'news')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('news.index', compact('news', 'featured', 'categories'));
    }

    /**
     * Afficher une actualité
     */
    public function show($slug)
    {
        $article = News::with(['user', 'category'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Incrémenter les vues
        $article->increment('views_count');

        // Articles similaires
        $related = News::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->where(function($q) use ($article) {
                $q->where('category_id', $article->category_id)
                  ->orWhere('type', $article->type);
            })
            ->limit(3)
            ->get();

        return view('news.show', compact('article', 'related'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $this->authorize('create', News::class);

        $categories = Category::where('type', 'news')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('news.create', compact('categories'));
    }

    /**
     * Enregistrer une nouvelle actualité
     */
    public function store(Request $request)
    {
        $this->authorize('create', News::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'type' => 'required|in:Actualité,Événement,Annonce,Culture',
            'category_id' => 'nullable|exists:categories,id',
            'event_date' => 'nullable|date',
            'event_location' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['title']);

        // Gérer les doublons de slug
        $originalSlug = $validated['slug'];
        $count = 1;
        while (News::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        // Convertir les tags en JSON
        if (isset($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
            $validated['tags'] = json_encode($tags);
        }

        // Statut selon le rôle
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'moderator') {
            $validated['status'] = 'published';
            $validated['published_at'] = now();
        } else {
            $validated['status'] = 'draft';
        }

        $news = News::create($validated);

        $message = $validated['status'] === 'published'
            ? 'Actualité publiée avec succès !'
            : 'Actualité créée en brouillon. Elle sera publiée après validation.';

        return redirect()->route('news.show', $news->slug)->with('success', $message);
    }

    /**
     * Mes actualités (dashboard)
     */
    public function myNews()
    {
        $articles = News::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total' => News::where('user_id', Auth::id())->count(),
            'published' => News::where('user_id', Auth::id())->where('status', 'published')->count(),
            'drafts' => News::where('user_id', Auth::id())->where('status', 'draft')->count(),
            'views' => News::where('user_id', Auth::id())->sum('views_count'),
        ];

        return view('news.my-news', compact('articles', 'stats'));
    }
}

