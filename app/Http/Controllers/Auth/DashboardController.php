<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Créer une nouvelle instance du contrôleur
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher le dashboard utilisateur
     */
    public function index()
    {
        $user = Auth::user();

        // Charger les relations
        $user->load([
            'skills',
            'experiences' => function ($query) {
                $query->orderBy('start_date', 'desc')->limit(5);
            },
            'educations' => function ($query) {
                $query->orderBy('start_date', 'desc')->limit(5);
            },
            'receivedMessages' => function ($query) {
                $query->where('is_read', false)->limit(5);
            }
        ]);

        // Statistiques
        $stats = [
            'total_messages' => $user->receivedMessages()->count(),
            'unread_messages' => $user->receivedMessages()->where('is_read', false)->count(),
            'opportunities_posted' => $user->opportunities()->count(),
            'news_posted' => $user->news()->count(),
        ];

        return view('dashboard.index', compact('user', 'stats'));
    }

    /**
     * Afficher le profil de l'utilisateur
     */
    public function profile()
    {
        $user = Auth::user();
        $user->load(['skills', 'experiences', 'educations']);

        return view('dashboard.profile', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition du profil
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('dashboard.edit-profile', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:M,F,Autre',
            'village_origin' => 'nullable|string|max:255',
            'quartier' => 'nullable|string|max:255',
            'current_city' => 'nullable|string|max:255',
            'current_country' => 'nullable|string|max:255',
            'current_address' => 'nullable|string',
            'current_profession' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'professional_status' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'skills_summary' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'open_to_opportunities' => 'boolean',
            'interests' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->route('dashboard.profile')
            ->with('success', 'Votre profil a été mis à jour avec succès !');
    }
}
