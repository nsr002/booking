@extends('layouts.admin')

@section('title', 'Booking Details')
@section('header', 'Booking #' . $booking->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Status Badge -->
        <div class="mb-6">
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'confirmed' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'cancelled' => 'bg-gray-100 text-gray-800',
                    'completed' => 'bg-blue-100 text-blue-800',
                ];
            @endphp
            <span class="px-4 py-2 inline-flex text-sm font-semibold rounded {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-800' }}">
                {{ ucfirst($booking->status) }}
            </span>
        </div>
        
        <!-- User Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Customer Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Name</p>
                    <p class="font-medium">{{ $booking->user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Phone</p>
                    <p class="font-medium">{{ $booking->user->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Email</p>
                    <p class="font-medium">{{ $booking->user->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">LINE User ID</p>
                    <p class="font-medium text-xs">{{ $booking->user->line_user_id ?? '-' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Schedule Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Schedule Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Trainer</p>
                    <p class="font-medium">{{ $booking->schedule->trainer->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Date</p>
                    <p class="font-medium">{{ $booking->schedule->date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Time</p>
                    <p class="font-medium">{{ $booking->schedule->start_time }} - {{ $booking->schedule->end_time }}</p>
                </div>
            </div>
        </div>
        
        <!-- Package Information -->
        @if($booking->userPackage)
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Package Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Package</p>
                    <p class="font-medium">{{ $booking->userPackage->package->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Credits Remaining</p>
                    <p class="font-medium">{{ $booking->userPackage->credits_remaining }}</p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Notes -->
        @if($booking->user_notes)
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">User Notes</h3>
            <p class="text-gray-700">{{ $booking->user_notes }}</p>
        </div>
        @endif
        
        @if($booking->admin_notes)
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Admin Notes</h3>
            <p class="text-gray-700">{{ $booking->admin_notes }}</p>
        </div>
        @endif
        
        <!-- Payment Slip -->
        @if($booking->payment_slip_path)
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Payment Slip</h3>
            <img src="{{ asset('storage/' . $booking->payment_slip_path) }}" alt="Payment Slip" class="max-w-md rounded-lg shadow">
        </div>
        @endif
        
        <!-- Actions -->
        @if($booking->status == 'pending')
        <div class="flex space-x-3">
            <form action="{{ route('admin.bookings.approve', $booking->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded hover:bg-green-600" onclick="return confirm('Approve this booking?')">
                    Approve
                </button>
            </form>
            
            <button onclick="openRejectModal()" class="px-6 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                Reject
            </button>
        </div>
        @endif
        
        @if($booking->status == 'confirmed')
        <form action="{{ route('admin.bookings.complete', $booking->id) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" onclick="return confirm('Mark as completed?')">
                Mark as Completed
            </button>
        </form>
        @endif
        
        <a href="{{ route('admin.bookings.index') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
            ← Back to Bookings
        </a>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Booking</h3>
        <form action="{{ route('admin.bookings.reject', $booking->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for rejection</label>
                <textarea name="admin_notes" rows="4" required class="w-full border rounded px-3 py-2" placeholder="Enter reason..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Reject
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
