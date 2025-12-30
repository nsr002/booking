<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * Get available schedules.
     */
    public function index(Request $request)
    {
        $query = Schedule::with('trainer')
            ->where('is_available', true)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc');

        // Filter by date if provided
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // Filter by trainer if provided
        if ($request->has('trainer_id')) {
            $query->where('trainer_id', $request->trainer_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $schedules = $query->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'trainer' => [
                    'id' => $schedule->trainer->id,
                    'name' => $schedule->trainer->name,
                    'avatar' => $schedule->trainer->avatar,
                    'specialty' => $schedule->trainer->specialty,
                ],
                'date' => $schedule->date->format('Y-m-d'),
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'max_bookings' => $schedule->max_bookings,
                'current_bookings' => $schedule->current_bookings,
                'available_slots' => $schedule->max_bookings - $schedule->current_bookings,
                'is_available' => $schedule->is_available && $schedule->canBeBooked(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }

    /**
     * Get a specific schedule.
     */
    public function show($id)
    {
        $schedule = Schedule::with('trainer')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $schedule->id,
                'trainer' => [
                    'id' => $schedule->trainer->id,
                    'name' => $schedule->trainer->name,
                    'avatar' => $schedule->trainer->avatar,
                    'specialty' => $schedule->trainer->specialty,
                    'bio' => $schedule->trainer->bio,
                ],
                'date' => $schedule->date->format('Y-m-d'),
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'max_bookings' => $schedule->max_bookings,
                'current_bookings' => $schedule->current_bookings,
                'available_slots' => $schedule->max_bookings - $schedule->current_bookings,
                'is_available' => $schedule->is_available && $schedule->canBeBooked(),
            ],
        ]);
    }
}
