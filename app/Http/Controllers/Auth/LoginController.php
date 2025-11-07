<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // Tentative de connexion
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Vérifier le statut du compte
            if ($user->status === 'pending') {
                Auth::logout();
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Votre compte est en attente de validation par un administrateur.');
            }

            if ($user->status === 'suspended') {
                Auth::logout();
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Votre compte a été suspendu. Veuillez contacter un administrateur.');
            }

            // Connexion réussie
            $request->session()->regenerate();

            // Redirection selon le rôle
            if ($user->role === 'admin' || $user->role === 'moderator') {
                return redirect()->intended('/admin/dashboard');
            }

            return redirect()->intended('/dashboard');
        }

        // Échec de connexion
        return redirect()->back()
            ->withInput($request->only('email'))
            ->with('error', 'Ces identifiants ne correspondent pas à nos enregistrements.');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}
