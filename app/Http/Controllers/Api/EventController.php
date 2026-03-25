<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::orderBy('event_date', 'asc')
            ->paginate(20);

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'event_date'  => 'required|date',
            'event_time'  => 'required|date_format:H:i',
            'location'    => 'required|string|max:255',
            'type'        => 'required|in:Prova,Concerto',
            'description' => 'nullable|string',
        ]);

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'event_date'  => 'sometimes|required|date',
            'event_time'  => 'sometimes|required|date_format:H:i',
            'location'    => 'sometimes|required|string|max:255',
            'type'        => 'sometimes|required|in:Prova,Concerto',
            'description' => 'nullable|string',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Evento eliminato.']);
    }
}
