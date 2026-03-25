<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function store(Request $request, $eventId)
    {
        Event::findOrFail($eventId);

        $validated = $request->validate([
            'status' => 'required|in:Presente,Assente,Giustificato',
            'notes'  => 'nullable|string|max:500',
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'user_id'  => $request->user()->id,
                'event_id' => $eventId,
            ],
            [
                'status' => $validated['status'],
                'notes'  => $validated['notes'] ?? null,
            ]
        );

        return response()->json($attendance, 200);
    }
}
