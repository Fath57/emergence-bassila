<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    /**
     * Affiche la landing page
     */
    public function index()
    {
        return view('landing');
    }
}
