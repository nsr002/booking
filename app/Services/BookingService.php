<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\UserPackage;
use App\Events\NewBookingEvent;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a new booking.
     */
    public function createBooking(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Check if schedule exists and is available
            $schedule = Schedule::findOrFail($data['schedule_id']);
            
            if (!$schedule->canBeBooked()) {
                throw new \Exception('This schedule is not available for booking.');
            }

            // If using credits, validate user package
            if (isset($data['user_package_id'])) {
                $userPackage = UserPackage::findOrFail($data['user_package_id']);
                
                if (!$userPackage->isValid()) {
                    throw new \Exception('Your package is not valid. It may be expired or have no credits remaining.');
                }
            }

            // Create booking
            $booking = Booking::create([
                'user_id' => $data['user_id'],
                'schedule_id' => $data['schedule_id'],
                'user_package_id' => $data['user_package_id'] ?? null,
                'status' => 'pending',
                'payment_slip_path' => $data['payment_slip_path'] ?? null,
                'user_notes' => $data['user_notes'] ?? null,
            ]);

            // Increment current bookings count
            $schedule->increment('current_bookings');

            // Check if schedule is now full
            if ($schedule->isFull()) {
                $schedule->update(['is_available' => false]);
            }

            // Load relationships for event
            $booking->load(['user', 'schedule.trainer']);

            // Broadcast new booking event
            broadcast(new NewBookingEvent($booking))->toOthers();

            return $booking;
        });
    }

    /**
     * Approve a booking.
     */
    public function approveBooking(Booking $booking, string $adminNotes = null)
    {
        return DB::transaction(function () use ($booking, $adminNotes) {
            // Deduct credit if using package
            if ($booking->user_package_id) {
                $userPackage = $booking->userPackage;
                
                if (!$userPackage->hasCredits()) {
                    throw new \Exception('No credits remaining in package.');
                }
                
                $userPackage->decrement('credits_remaining');
            }

            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            return $booking;
        });
    }

    /**
     * Reject a booking.
     */
    public function rejectBooking(Booking $booking, string $adminNotes = null)
    {
        return DB::transaction(function () use ($booking, $adminNotes) {
            $booking->update([
                'status' => 'rejected',
                'admin_notes' => $adminNotes,
            ]);

            // Restore schedule availability
            $schedule = $booking->schedule;
            $schedule->decrement('current_bookings');
            
            if (!$schedule->isFull() && $schedule->date >= now()->toDateString()) {
                $schedule->update(['is_available' => true]);
            }

            return $booking;
        });
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking)
    {
        return DB::transaction(function () use ($booking) {
            if (!$booking->canBeCancelled()) {
                throw new \Exception('This booking cannot be cancelled.');
            }

            // Refund credit if booking was confirmed
            if ($booking->status === 'confirmed' && $booking->user_package_id) {
                $booking->userPackage->increment('credits_remaining');
            }

            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Restore schedule availability
            $schedule = $booking->schedule;
            $schedule->decrement('current_bookings');
            
            if (!$schedule->isFull() && $schedule->date >= now()->toDateString()) {
                $schedule->update(['is_available' => true]);
            }

            return $booking;
        });
    }

    /**
     * Complete a booking.
     */
    public function completeBooking(Booking $booking)
    {
        $booking->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $booking;
    }

    /**
     * Get user's booking history.
     */
    public function getUserBookings(int $userId)
    {
        return Booking::where('user_id', $userId)
            ->with(['schedule.trainer', 'userPackage.package'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
