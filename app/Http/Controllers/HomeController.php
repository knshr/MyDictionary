<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return redirect('/dashboard');
        }
        
        return Inertia::render('Home');
    }
}
