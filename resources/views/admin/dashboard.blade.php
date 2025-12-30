@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Today's Bookings -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-1">
                <p class="text-gray-500 text-sm">Today's Bookings</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['today_bookings'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-day text-blue-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Pending Bookings -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-1">
                <p class="text-gray-500 text-sm">Pending Bookings</p>
                <p class="text-3xl font-bold text-orange-600">{{ $stats['pending_bookings'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-orange-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Users -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-1">
                <p class="text-gray-500 text-sm">Total Users</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['total_users'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-green-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Today's Revenue -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-1">
                <p class="text-gray-500 text-sm">Today's Revenue</p>
                <p class="text-3xl font-bold text-gray-800">฿{{ number_format($stats['today_revenue'], 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-dollar-sign text-purple-500 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Bookings -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Recent Bookings</h3>
        </div>
        <div class="p-6">
            @if($recentBookings->count() > 0)
                <div class="space-y-4">
                    @foreach($recentBookings as $booking)
                        <div class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0">
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">{{ $booking->user->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $booking->schedule->trainer->name }} - 
                                    {{ $booking->schedule->date->format('d/m/Y') }} 
                                    {{ $booking->schedule->start_time }}
                                </p>
                            </div>
                            <div>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No recent bookings</p>
            @endif
        </div>
    </div>
    
    <!-- Upcoming Schedules -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Upcoming Schedules</h3>
        </div>
        <div class="p-6">
            @if($upcomingSchedules->count() > 0)
                <div class="space-y-4">
                    @foreach($upcomingSchedules as $schedule)
                        <div class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0">
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">{{ $schedule->trainer->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $schedule->date->format('d/m/Y') }} 
                                    {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $schedule->current_bookings }}/{{ $schedule->max_bookings }}
                                </p>
                                <p class="text-xs text-gray-500">Bookings</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No upcoming schedules</p>
            @endif
        </div>
    </div>
</div>
@endsection
