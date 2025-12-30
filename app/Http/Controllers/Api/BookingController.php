<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BookingService;
use App\Models\Booking;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Get user's bookings.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $bookings = $this->bookingService->getUserBookings($user->id);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Create a new booking.
     */
    public function store(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'user_package_id' => 'nullable|exists:user_packages,id',
            'payment_slip' => 'nullable|image|max:5120', // 5MB
            'user_notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $data = [
            'user_id' => $user->id,
            'schedule_id' => $request->schedule_id,
            'user_package_id' => $request->user_package_id,
            'user_notes' => $request->user_notes,
        ];

        // Handle payment slip upload
        if ($request->hasFile('payment_slip')) {
            $path = $request->file('payment_slip')->store('payment-slips', 'public');
            $data['payment_slip_path'] = $path;
        }

        try {
            $booking = $this->bookingService->createBooking($data);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking->load(['schedule.trainer', 'userPackage.package']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get a specific booking.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $booking = Booking::with(['schedule.trainer', 'userPackage.package'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $booking = Booking::where('user_id', $user->id)->findOrFail($id);

        try {
            $booking = $this->bookingService->cancelBooking($booking);

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'data' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
