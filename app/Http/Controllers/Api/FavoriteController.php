<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Auth::user()->favorites()->latest()->get();
        return response()->json(['success' => true, 'data' => $favorites]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'word' => 'required|string',
            'definition' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        $favorite = Auth::user()->favorites()->create($data);
        return response()->json(['success' => true, 'data' => $favorite], 201);
    }

    public function update(Request $request, Favorite $favorite)
    {
        $this->authorize('update', $favorite);
        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);
        $favorite->update($data);
        return response()->json(['success' => true, 'data' => $favorite]);
    }

    public function destroy(Favorite $favorite)
    {
        $this->authorize('delete', $favorite);
        $favorite->delete();
        return response()->json(['success' => true]);
    }
}
