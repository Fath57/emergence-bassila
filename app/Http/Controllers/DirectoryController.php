<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    /**
     * Display the members directory.
     */
    public function index(Request $request)
    {
        $query = User::with(['profile', 'skills'])
            ->approved()
            ->whereHas('profile', function($q) {
                $q->where('visible_annuaire', true);
            });

        // Filtres de recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('profile', function($subQuery) use ($search) {
                      $subQuery->where('profession', 'like', "%{$search}%")
                               ->orWhere('ville_actuelle', 'like', "%{$search}%")
                               ->orWhere('pays_actuel', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('pays')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('pays_actuel', $request->pays);
            });
        }

        if ($request->filled('domaine')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('domaine_expertise', 'like', "%{$request->domaine}%");
            });
        }

        if ($request->filled('skill')) {
            $query->whereHas('skills', function($q) use ($request) {
                $q->where('skills.id', $request->skill);
            });
        }

        // Disponibilité pour opportunités
        if ($request->filled('disponible')) {
            $query->whereHas('profile', function($q) {
                $q->where('disponible_opportunites', true);
            });
        }

        $members = $query->latest()->paginate(12);
        $skills = Skill::orderBy('name')->get();

        // Liste des pays uniques pour le filtre
        $countries = User::approved()
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->whereNotNull('user_profiles.pays_actuel')
            ->distinct()
            ->pluck('user_profiles.pays_actuel');

        return view('directory.index', compact('members', 'skills', 'countries'));
    }

    /**
     * Show a specific member's profile.
     */
    public function show(User $user)
    {
        if (!$user->is_approved || !$user->profile || !$user->profile->visible_annuaire) {
            abort(404);
        }

        $user->load(['profile', 'skills']);

        return view('directory.show', compact('user'));
    }
}
