<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div id="app" class="min-h-screen">
        <!-- Header -->
        <header class="bg-blue-600 text-white p-4">
            <h1 class="text-xl font-bold">Training Packages</h1>
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
            <!-- My Credits -->
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <h2 class="font-semibold text-gray-900 mb-2">My Credits</h2>
                <p class="text-3xl font-bold text-blue-600" id="totalCredits">0</p>
                <p class="text-sm text-gray-600">Available credits</p>
            </div>
            
            <!-- My Packages -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3">My Active Packages</h2>
                <div id="myPackagesList" class="space-y-3">
                    <!-- User packages will be loaded here -->
                </div>
            </div>
            
            <!-- Available Packages -->
            <div>
                <h2 class="text-lg font-semibold mb-3">Available Packages</h2>
                <div id="packagesList" class="space-y-3">
                    <!-- Packages will be loaded here -->
                </div>
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
                        loadData();
                    });
                }
            }).catch((err) => {
                alert('LIFF initialization failed: ' + err);
            });
        });
        
        // Load all data
        function loadData() {
            Promise.all([
                loadPackages(),
                loadMyPackages()
            ]).then(() => {
                document.getElementById('loading').classList.add('hidden');
                document.getElementById('content').classList.remove('hidden');
            });
        }
        
        // Load available packages
        function loadPackages() {
            return axios.get('/api/packages', {
                headers: { 'Authorization': `Bearer ${liffToken}` }
            }).then(response => {
                displayPackages(response.data.data);
            });
        }
        
        // Load user packages
        function loadMyPackages() {
            return axios.get('/api/user/credits', {
                headers: { 'Authorization': `Bearer ${liffToken}` }
            }).then(response => {
                document.getElementById('totalCredits').textContent = response.data.data.total_credits;
                displayMyPackages(response.data.data.packages);
            });
        }
        
        // Display available packages
        function displayPackages(packages) {
            const container = document.getElementById('packagesList');
            container.innerHTML = '';
            
            if (packages.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No packages available</p>';
                return;
            }
            
            packages.forEach(pkg => {
                const div = document.createElement('div');
                div.className = 'bg-white rounded-lg shadow p-4';
                div.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">${pkg.name}</h3>
                            <p class="text-sm text-gray-600 mt-1">${pkg.description || ''}</p>
                            <div class="mt-3 space-y-1">
                                <p class="text-sm">
                                    <span class="text-gray-600">Credits:</span>
                                    <span class="font-semibold">${pkg.total_credits}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="text-gray-600">Valid for:</span>
                                    <span class="font-semibold">${pkg.days_valid} days</span>
                                </p>
                                <p class="text-2xl font-bold text-blue-600 mt-2">฿${parseFloat(pkg.price).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                    <button onclick="purchasePackage(${pkg.id})" class="mt-4 w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                        Purchase
                    </button>
                `;
                container.appendChild(div);
            });
        }
        
        // Display user packages
        function displayMyPackages(packages) {
            const container = document.getElementById('myPackagesList');
            container.innerHTML = '';
            
            if (packages.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No active packages</p>';
                return;
            }
            
            packages.forEach(pkg => {
                const expiresAt = new Date(pkg.expires_at);
                const div = document.createElement('div');
                div.className = 'bg-green-50 border border-green-200 rounded-lg p-4';
                div.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">${pkg.package.name}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="font-semibold text-green-600">${pkg.credits_remaining}</span> credits remaining
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Expires: ${expiresAt.toLocaleDateString()}
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded ${
                            pkg.payment_status === 'approved' ? 'bg-green-100 text-green-800' : 
                            pkg.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                            'bg-red-100 text-red-800'
                        }">
                            ${pkg.payment_status.toUpperCase()}
                        </span>
                    </div>
                `;
                container.appendChild(div);
            });
        }
        
        // Purchase package
        function purchasePackage(packageId) {
            alert('Please contact admin to purchase packages. Payment slip upload will be implemented in production.');
            // In production, implement payment slip upload functionality
        }
    </script>
</body>
</html>
