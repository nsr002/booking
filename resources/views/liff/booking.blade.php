<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Training - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div id="app" class="min-h-screen">
        <!-- Header -->
        <header class="bg-blue-600 text-white p-4">
            <h1 class="text-xl font-bold">Book Training Session</h1>
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
            <!-- User Info -->
            <div id="userInfo" class="bg-white rounded-lg shadow p-4 mb-4">
                <div class="flex items-center">
                    <img id="userAvatar" src="" alt="Avatar" class="w-12 h-12 rounded-full mr-3">
                    <div>
                        <p id="userName" class="font-semibold"></p>
                        <p id="userCredits" class="text-sm text-gray-600"></p>
                    </div>
                </div>
            </div>
            
            <!-- Credits Info -->
            <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-4 rounded">
                <p class="font-semibold text-green-800">Available Credits</p>
                <p id="totalCredits" class="text-2xl font-bold text-green-800">0</p>
            </div>
            
            <!-- Schedules -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold mb-3">Available Schedules</h2>
                <div id="schedulesList" class="space-y-3">
                    <!-- Schedules will be loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Error -->
        <div id="error" class="hidden p-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <p id="errorMessage"></p>
            </div>
        </div>
    </div>
    
    <!-- Booking Modal -->
    <div id="bookingModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Booking</h3>
            <div id="selectedScheduleInfo" class="mb-4 p-3 bg-gray-50 rounded">
                <!-- Schedule info will be displayed here -->
            </div>
            <form id="bookingForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Package (Optional)</label>
                    <select id="packageSelect" class="w-full border rounded px-3 py-2">
                        <option value="">Pay separately</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea id="userNotes" rows="3" class="w-full border rounded px-3 py-2" placeholder="Any special requests..."></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeBookingModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let liffToken = '';
        let userData = null;
        let schedules = [];
        let userPackages = [];
        let selectedSchedule = null;
        
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
                        authenticateUser();
                    });
                }
            }).catch((err) => {
                showError('LIFF initialization failed: ' + err);
            });
        });
        
        // Authenticate user
        function authenticateUser() {
            axios.post('/api/liff/auth', {
                access_token: liffToken
            }).then(response => {
                if (response.data.success) {
                    userData = response.data.data.user;
                    loadData();
                }
            }).catch(error => {
                showError('Authentication failed');
            });
        }
        
        // Load all data
        function loadData() {
            Promise.all([
                loadSchedules(),
                loadCredits(),
                loadUserPackages()
            ]).then(() => {
                displayUserInfo();
                displaySchedules();
                document.getElementById('loading').classList.add('hidden');
                document.getElementById('content').classList.remove('hidden');
            }).catch(error => {
                showError('Failed to load data');
            });
        }
        
        // Load schedules
        function loadSchedules() {
            return axios.get('/api/schedules', {
                headers: { 'Authorization': `Bearer ${liffToken}` }
            }).then(response => {
                schedules = response.data.data;
            });
        }
        
        // Load credits
        function loadCredits() {
            return axios.get('/api/user/credits', {
                headers: { 'Authorization': `Bearer ${liffToken}` }
            }).then(response => {
                document.getElementById('totalCredits').textContent = response.data.data.total_credits;
            });
        }
        
        // Load user packages
        function loadUserPackages() {
            return axios.get('/api/user/credits', {
                headers: { 'Authorization': `Bearer ${liffToken}` }
            }).then(response => {
                userPackages = response.data.data.packages;
            });
        }
        
        // Display user info
        function displayUserInfo() {
            document.getElementById('userName').textContent = userData.name;
            document.getElementById('userAvatar').src = userData.avatar || 'https://via.placeholder.com/48';
        }
        
        // Display schedules
        function displaySchedules() {
            const container = document.getElementById('schedulesList');
            container.innerHTML = '';
            
            if (schedules.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No available schedules</p>';
                return;
            }
            
            schedules.forEach(schedule => {
                const div = document.createElement('div');
                div.className = 'bg-white rounded-lg shadow p-4';
                div.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">${schedule.trainer.name}</p>
                            <p class="text-sm text-gray-600">${schedule.trainer.specialty || ''}</p>
                            <p class="text-sm text-gray-600 mt-2">
                                📅 ${schedule.date}<br>
                                ⏰ ${schedule.start_time} - ${schedule.end_time}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${schedule.available_slots} slot(s) available
                            </p>
                        </div>
                        <button onclick="openBookingModal(${schedule.id})" 
                                class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
                                ${!schedule.is_available ? 'disabled' : ''}>
                            Book
                        </button>
                    </div>
                `;
                container.appendChild(div);
            });
        }
        
        // Open booking modal
        function openBookingModal(scheduleId) {
            selectedSchedule = schedules.find(s => s.id === scheduleId);
            if (!selectedSchedule) return;
            
            // Display schedule info
            document.getElementById('selectedScheduleInfo').innerHTML = `
                <p class="font-semibold">${selectedSchedule.trainer.name}</p>
                <p class="text-sm text-gray-600">${selectedSchedule.date} ${selectedSchedule.start_time} - ${selectedSchedule.end_time}</p>
            `;
            
            // Populate package select
            const packageSelect = document.getElementById('packageSelect');
            packageSelect.innerHTML = '<option value="">Pay separately</option>';
            userPackages.forEach(pkg => {
                if (pkg.credits_remaining > 0) {
                    packageSelect.innerHTML += `
                        <option value="${pkg.id}">
                            ${pkg.package.name} (${pkg.credits_remaining} credits)
                        </option>
                    `;
                }
            });
            
            document.getElementById('bookingModal').classList.remove('hidden');
        }
        
        // Close booking modal
        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
            document.getElementById('bookingForm').reset();
        }
        
        // Submit booking
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('schedule_id', selectedSchedule.id);
            
            const packageId = document.getElementById('packageSelect').value;
            if (packageId) {
                formData.append('user_package_id', packageId);
            }
            
            const notes = document.getElementById('userNotes').value;
            if (notes) {
                formData.append('user_notes', notes);
            }
            
            axios.post('/api/bookings', formData, {
                headers: { 
                    'Authorization': `Bearer ${liffToken}`,
                    'Content-Type': 'multipart/form-data'
                }
            }).then(response => {
                if (response.data.success) {
                    alert('Booking submitted successfully! Waiting for confirmation.');
                    closeBookingModal();
                    loadData();
                }
            }).catch(error => {
                alert('Booking failed: ' + (error.response?.data?.message || 'Unknown error'));
            });
        });
        
        // Show error
        function showError(message) {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('content').classList.add('hidden');
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('error').classList.remove('hidden');
        }
    </script>
</body>
</html>
