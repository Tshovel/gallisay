<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Repertoire;
use Illuminate\Http\Request;

class RepertoireController extends Controller
{
    public function index(Request $request)
    {
        $query = Repertoire::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('composer', 'like', "%{$search}%");
            });
        }

        if ($request->filled('period')) {
            $query->where('period_tag', $request->period);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty_tag', $request->difficulty);
        }

        $scores = $query->orderBy('title')->paginate(20);

        return response()->json($scores);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'composer'       => 'required|string|max:255',
            'pdf_path'       => 'nullable|string|max:500',
            'audio_url'      => 'nullable|url|max:500',
            'period_tag'     => 'required|string|max:100',
            'difficulty_tag' => 'required|in:Facile,Medio,Difficile',
        ]);

        $score = Repertoire::create($validated);

        return response()->json($score, 201);
    }

    public function update(Request $request, $id)
    {
        $score = Repertoire::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'sometimes|required|string|max:255',
            'composer'       => 'sometimes|required|string|max:255',
            'pdf_path'       => 'nullable|string|max:500',
            'audio_url'      => 'nullable|url|max:500',
            'period_tag'     => 'sometimes|required|string|max:100',
            'difficulty_tag' => 'sometimes|required|in:Facile,Medio,Difficile',
        ]);

        $score->update($validated);

        return response()->json($score);
    }

    public function destroy($id)
    {
        $score = Repertoire::findOrFail($id);
        $score->delete();

        return response()->json(['message' => 'Partitura eliminata.']);
    }
}
