<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Annuaire des membres
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->where('status', 'active')
            ->where('profile_visible', true)
            ->with(['skills', 'experiences', 'educations']);

        // Statistiques globales
        $stats = [
            'total' => User::where('status', 'active')->count(),
            'countries' => User::where('status', 'active')
                ->whereNotNull('current_country')
                ->distinct('current_country')
                ->count('current_country'),
            'cities' => User::where('status', 'active')
                ->whereNotNull('current_city')
                ->distinct('current_city')
                ->count('current_city'),
        ];

        // Pagination
        $members = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('members.index', compact('members', 'stats'));
    }

    /**
     * Recherche avancée
     */
    public function search(Request $request)
    {
        $query = User::query()
            ->where('status', 'active')
            ->where('profile_visible', true);

        // Recherche par nom
        if ($request->filled('name')) {
            $name = $request->name;
            $query->where(function($q) use ($name) {
                $q->where('first_name', 'like', "%{$name}%")
                  ->orWhere('last_name', 'like', "%{$name}%");
            });
        }

        // Filtre par ville
        if ($request->filled('city')) {
            $query->where('current_city', 'like', "%{$request->city}%");
        }

        // Filtre par pays
        if ($request->filled('country')) {
            $query->where('current_country', 'like', "%{$request->country}%");
        }

        // Filtre par village d'origine
        if ($request->filled('village')) {
            $query->where('village_origin', 'like', "%{$request->village}%");
        }

        // Filtre par profession
        if ($request->filled('profession')) {
            $query->where('current_profession', 'like', "%{$request->profession}%");
        }

        // Filtre par compétence
        if ($request->filled('skill')) {
            $query->whereHas('skills', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->skill}%");
            });
        }

        // Filtre ouvert aux opportunités
        if ($request->filled('open_to_opportunities')) {
            $query->where('open_to_opportunities', true);
        }

        $members = $query->with(['skills', 'experiences'])
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->appends($request->except('page'));

        // Données pour les filtres
        $countries = User::where('status', 'active')
            ->whereNotNull('current_country')
            ->distinct()
            ->pluck('current_country')
            ->sort();

        $cities = User::where('status', 'active')
            ->whereNotNull('current_city')
            ->distinct()
            ->pluck('current_city')
            ->sort();

        return view('members.search', compact('members', 'countries', 'cities'));
    }

    /**
     * Afficher le profil public d'un membre
     */
    public function show($id)
    {
        $user = User::where('id', $id)
            ->where('status', 'active')
            ->where('profile_visible', true)
            ->with(['skills', 'experiences' => function($q) {
                $q->orderBy('start_date', 'desc');
            }, 'educations' => function($q) {
                $q->orderBy('start_date', 'desc');
            }])
            ->firstOrFail();

        return view('members.show', compact('user'));
    }
}
