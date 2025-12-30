<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\LineService;
use App\Events\BookingStatusUpdated;

class BookingManageController extends Controller
{
    protected $bookingService;
    protected $lineService;

    public function __construct(BookingService $bookingService, LineService $lineService)
    {
        $this->bookingService = $bookingService;
        $this->lineService = $lineService;
    }

    /**
     * Display bookings list.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'schedule.trainer', 'userPackage.package']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->whereDate('date', $request->date);
            });
        }

        // Filter by trainer
        if ($request->has('trainer_id')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('trainer_id', $request->trainer_id);
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Display booking details.
     */
    public function show($id)
    {
        $booking = Booking::with(['user', 'schedule.trainer', 'userPackage.package'])->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Approve a booking.
     */
    public function approve(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        try {
            $this->bookingService->approveBooking($booking, $request->admin_notes);

            // Send LINE notification
            if ($booking->user->line_user_id) {
                $this->lineService->sendBookingConfirmation($booking->user->line_user_id, $booking);
            }

            // Broadcast event
            broadcast(new BookingStatusUpdated($booking, 'Your booking has been confirmed!'))->toOthers();

            return redirect()->back()->with('success', 'Booking approved successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a booking.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $booking = Booking::findOrFail($id);

        try {
            $this->bookingService->rejectBooking($booking, $request->admin_notes);

            // Send LINE notification
            if ($booking->user->line_user_id) {
                $this->lineService->sendBookingRejection($booking->user->line_user_id, $booking, $request->admin_notes);
            }

            // Broadcast event
            broadcast(new BookingStatusUpdated($booking, 'Your booking has been rejected.'))->toOthers();

            return redirect()->back()->with('success', 'Booking rejected');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete a booking.
     */
    public function complete($id)
    {
        $booking = Booking::findOrFail($id);

        try {
            $this->bookingService->completeBooking($booking);

            return redirect()->back()->with('success', 'Booking marked as completed');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
