<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accounting;
use App\Models\Attendance;
use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function stats()
    {
        $data = Cache::remember('dashboard_stats', 300, function () {
            // Payment status breakdown
            $pagato   = Accounting::where('status', 'Pagato')->count();
            $inAttesa = Accounting::where('status', 'In attesa')->count();

            // Attendance percentage for last 5 rehearsals (Prova type)
            $lastRehearsals = Event::where('type', 'Prova')
                ->orderBy('event_date', 'desc')
                ->limit(5)
                ->get();

            $attendanceStats = $lastRehearsals->map(function (Event $event) {
                $total   = Attendance::where('event_id', $event->id)->count();
                $present = Attendance::where('event_id', $event->id)
                    ->where('status', 'Presente')
                    ->count();

                $percentage = $total > 0 ? round(($present / $total) * 100) : 0;

                return [
                    'event'      => $event->title . ' (' . $event->event_date->format('d/m/Y') . ')',
                    'percentage' => $percentage,
                ];
            });

            return [
                'payments' => [
                    'pagato'    => $pagato,
                    'in_attesa' => $inAttesa,
                ],
                'attendance' => $attendanceStats,
            ];
        });

        return response()->json($data);
    }
}
