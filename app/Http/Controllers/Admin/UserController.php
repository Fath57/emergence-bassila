<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gallery;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display pending user approvals.
     */
    public function index()
    {
        $pendingUsers = User::with('profile')
            ->where('is_approved', false)
            ->latest()
            ->paginate(20);

        $pendingGallery = Gallery::with('user')
            ->where('is_approved', false)
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('pendingUsers', 'pendingGallery'));
    }

    /**
     * Approve a user.
     */
    public function approve(User $user)
    {
        $user->update(['is_approved' => true]);

        return back()->with('success', "L'utilisateur {$user->name} a été approuvé.");
    }

    /**
     * Reject a user.
     */
    public function reject(User $user)
    {
        $user->delete();

        return back()->with('success', "L'utilisateur a été rejeté et supprimé.");
    }

    /**
     * Toggle admin status.
     */
    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre statut admin.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->is_admin ? 'administrateur' : 'utilisateur normal';
        return back()->with('success', "Le statut de {$user->name} a été changé en {$status}.");
    }

    /**
     * Approve a gallery item.
     */
    public function approveGallery(Gallery $gallery)
    {
        $gallery->update(['is_approved' => true]);

        return back()->with('success', "L'image '{$gallery->title}' a été approuvée.");
    }

    /**
     * Reject a gallery item.
     */
    public function rejectGallery(Gallery $gallery)
    {
        $gallery->delete();

        return back()->with('success', "L'image a été rejetée et supprimée.");
    }
}
