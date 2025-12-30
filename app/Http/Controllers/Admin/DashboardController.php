<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\Package;
use App\Models\Schedule;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard.
     */
    public function index()
    {
        $stats = [
            'today_bookings' => Booking::whereDate('created_at', today())->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_users' => User::where('role', 'user')->count(),
            'active_packages' => Package::where('is_active', true)->count(),
            'today_revenue' => Booking::whereDate('created_at', today())
                ->where('status', 'confirmed')
                ->join('user_packages', 'bookings.user_package_id', '=', 'user_packages.id')
                ->join('packages', 'user_packages.package_id', '=', 'packages.id')
                ->sum('packages.price'),
        ];

        $recentBookings = Booking::with(['user', 'schedule.trainer'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $upcomingSchedules = Schedule::with('trainer')
            ->where('date', '>=', today())
            ->where('is_available', true)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'upcomingSchedules'));
    }
}
