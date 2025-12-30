<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Trainer;

class ScheduleManageController extends Controller
{
    /**
     * Display schedules list.
     */
    public function index()
    {
        $schedules = Schedule::with('trainer')
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Show create schedule form.
     */
    public function create()
    {
        $trainers = Trainer::where('is_active', true)->get();

        return view('admin.schedules.create', compact('trainers'));
    }

    /**
     * Store new schedule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|exists:trainers,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_bookings' => 'required|integer|min:1',
        ]);

        Schedule::create($request->all());

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule created successfully');
    }

    /**
     * Show edit schedule form.
     */
    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $trainers = Trainer::where('is_active', true)->get();

        return view('admin.schedules.edit', compact('schedule', 'trainers'));
    }

    /**
     * Update schedule.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'trainer_id' => 'required|exists:trainers,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_bookings' => 'required|integer|min:1',
            'is_available' => 'required|boolean',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->all());

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule updated successfully');
    }

    /**
     * Delete schedule.
     */
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        
        if ($schedule->bookings()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete schedule with existing bookings');
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule deleted successfully');
    }
}
