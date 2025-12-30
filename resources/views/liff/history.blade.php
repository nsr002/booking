<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div id="app" class="min-h-screen">
        <!-- Header -->
        <header class="bg-blue-600 text-white p-4">
            <h1 class="text-xl font-bold">Booking History</h1>
        </header>
        
        <!-- Loading -->
        <div id="loading" class="flex items-center justify-center h-64">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Loading...</p>
            </div>
        </div>
        
        <!-- Main Content -->
        <div id="content" class="hidden p-4">
            <div id="bookingsList" class="space-y-3">
                <!-- Bookings will be loaded here -->
            </div>
        </div>
    </div>
    
    <script>
        let liffToken = '';
        
        // Initialize LIFF
        window.addEventListener('load', function() {
            liff.init({
                liffId: '{{ config("line.liff_id") }}'
            }).then(() => {
                if (!liff.isLoggedIn()) {
                    liff.login();
                } else {
                    liff.getAccessToken().then(token => {
                        liffToken = token;
                        loadBookings();
                    });
                }
            }).catch((err) => {
                alert('LIFF initialization failed: ' + err);
            });
        });
        
        // Load bookings
        function loadBookings() {
            axios.get('/api/bookings/my', {
                headers: { 'Authorization': `Bearer ${liffToken}` }
            }).then(response => {
                displayBookings(response.data.data);
                document.getElementById('loading').classList.add('hidden');
                document.getElementById('content').classList.remove('hidden');
            }).catch(error => {
                alert('Failed to load bookings');
            });
        }
        
        // Display bookings
        function displayBookings(bookings) {
            const container = document.getElementById('bookingsList');
            container.innerHTML = '';
            
            if (bookings.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No bookings found</p>';
                return;
            }
            
            const statusColors = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'confirmed': 'bg-green-100 text-green-800',
                'rejected': 'bg-red-100 text-red-800',
                'cancelled': 'bg-gray-100 text-gray-800',
                'completed': 'bg-blue-100 text-blue-800'
            };
            
            bookings.forEach(booking => {
                const div = document.createElement('div');
                div.className = 'bg-white rounded-lg shadow p-4';
                div.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold text-gray-900">${booking.schedule.trainer.name}</p>
                            <p class="text-sm text-gray-600">
                                ${booking.schedule.date}<br>
                                ${booking.schedule.start_time} - ${booking.schedule.end_time}
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded ${statusColors[booking.status] || 'bg-gray-100 text-gray-800'}">
                            ${booking.status.toUpperCase()}
                        </span>
                    </div>
                    ${booking.admin_notes ? `<p class="text-sm text-gray-600 mt-2">Note: ${booking.admin_notes}</p>` : ''}
                    ${booking.status === 'pending' || booking.status === 'confirmed' ? `
                        <button onclick="cancelBooking(${booking.id})" class="mt-3 w-full bg-red-500 text-white py-2 rounded text-sm">
                            Cancel Booking
                        </button>
                    ` : ''}
                `;
                container.appendChild(div);
            });
        }
        
        // Cancel booking
        function cancelBooking(bookingId) {
            if (!confirm('Are you sure you want to cancel this booking?')) return;
            
            axios.post(`/api/bookings/${bookingId}/cancel`, {}, {
                headers: { 'Authorization': `Bearer ${liffToken}` }
            }).then(response => {
                if (response.data.success) {
                    alert('Booking cancelled successfully');
                    loadBookings();
                }
            }).catch(error => {
                alert('Failed to cancel booking: ' + (error.response?.data?.message || 'Unknown error'));
            });
        }
    </script>
</body>
</html>
