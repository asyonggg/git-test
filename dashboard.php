<?php
session_start(); // Must be at the top

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_data']) || $_SESSION['user_data']['role'] !== 'admin') {
    // Not logged in or not an admin, redirect to login page
    header('Location: index.html?error=auth_required');
    exit;
}

// Get user data from session
$user = $_SESSION['user_data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JRU-A-PULSE Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jru-blue': '#1e3a8a',
                        'jru-gold': '#f59e0b',
                    }
                }
            }
        }
    </script>
    <style>
    /* Add a simple loader */
    .loader {
        border: 4px solid #f3f3f3;
        border-radius: 50%;
        border-top: 4px solid #1e3a8a;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .chart-container.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #1e3a8a;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    /* Sidebar styles from original file... */
    .sidebar-transition{transition:all .3s cubic-bezier(.4,0,.2,1)}.chart-container{position:relative;height:220px;width:100%}@media (max-width:768px){.chart-container{height:180px}}.sidebar-collapsed{width:5rem}.sidebar-expanded{width:16rem}.menu-text{transition:opacity .2s ease-in-out}.logo-transition{transition:all .3s ease}::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:#f1f1f1}::-webkit-scrollbar-thumb{background:#c1c1c1;border-radius:3px}::-webkit-scrollbar-thumb:hover{background:#a1a1a1}
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-transition sidebar-expanded bg-blue-950 shadow-lg flex flex-col border-r border-gray-200">
            <!-- Logo, Nav, Profile sections remain the same -->
            <div class="p-4 border-b border-gray-200"><div class="flex items-center"><button id="sidebarToggle" class="p-2 rounded-lg hover:bg-gray-600 transition-colors mr-3"><i class="fas fa-bars text-gray-100"></i></button><div id="logoContainer" class="logo-transition flex items-center"><img src="assets\jru-pulse-final-white.png" alt="JRU-A-PULSE" class="h-8 w-auto"></div></div></div><nav class="flex-1 p-4 overflow-y-auto"><ul class="space-y-2"><li><a href="dashboard.php" class="flex items-center px-3 py-3 bg-blue-50 text-jru-blue rounded-lg font-medium"><i class="fas fa-tachometer-alt text-lg w-6"></i><span class="menu-text ml-3">Dashboard</span></a></li><li><a href="survey-management.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-poll text-lg w-6"></i><span class="menu-text ml-3">Survey Management</span></a></li><li><a href="performance-analytics-reports.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-chart-line text-lg w-6"></i><span class="menu-text ml-3">Performance Analytics & Reports</span></a></li><li><a href="#" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-users text-lg w-6"></i><span class="menu-text ml-3">User Management</span></a></li><li><a href="#" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-cog text-lg w-6"></i><span class="menu-text ml-3">Settings</span></a></li></ul><br><div class="mt-8"><div id="quickActionsHeader" class="menu-text text-xs font-semibold text-gray-50 uppercase tracking-wider mb-3">Quick Actions</div><div class="space-y-2"><button class="flex items-center w-full px-3 py-2 text-sm text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-plus text-sm w-6"></i><span class="menu-text ml-3">New Survey</span></button><button class="flex items-center w-full px-3 py-2 text-sm text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-download text-sm w-6"></i><span class="menu-text ml-3">Export Data</span></button></div></div></nav><div class="p-4 border-t border-gray-700"><div class="flex items-center"><?php if (isset($user['picture']) && !empty($user['picture'])): ?><img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="User Picture" class="w-10 h-10 rounded-full object-cover mr-3"><?php else: ?><div class="w-10 h-10 bg-gradient-to-r from-jru-gold to-yellow-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-3"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div><?php endif; ?><div id="userInfo" class="menu-text ml-0 flex-1 overflow-hidden"><p class="text-sm font-medium truncate text-white" title="<?php echo htmlspecialchars($user['name']); ?>"><?php echo htmlspecialchars($user['name']); ?></p><p class="text-xs text-gray-400 truncate" title="<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></p></div><a href="logout.php" class="menu-text p-2 text-gray-50 hover:text-yellow-400 transition-colors"><i class="fas fa-sign-out-alt"></i></a></div></div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
                        <p class="text-sm text-gray-600 mt-1">Performance and User-satisfaction Linked Services Evaluation</p>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Controls -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6 space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600 font-medium">Date Range:</span>
                        <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                        <span class="text-gray-400">to</span>
                        <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                    </div>
                </div>
                
                <!-- Key Metrics -->
                <div id="metrics-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Cards will be populated by JavaScript -->
                    <!-- Overall Satisfaction -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200"><div class="flex items-center justify-between mb-3"><div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-smile text-jru-blue text-lg"></i></div></div><div><p class="text-sm font-medium text-gray-600 mb-1">Overall Satisfaction</p><div class="flex items-center"><span id="overall-satisfaction-score" class="text-2xl font-bold text-gray-900 mr-2">...</span><div id="overall-satisfaction-stars" class="flex text-jru-gold"></div></div></div></div>
                    <!-- Total Responses -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200"><div class="flex items-center justify-between mb-3"><div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"><i class="fas fa-chart-bar text-green-600 text-lg"></i></div></div><div><p class="text-sm font-medium text-gray-600 mb-1">Total Responses</p><p id="total-responses" class="text-2xl font-bold text-gray-900">...</p></div></div>
                    <!-- Feedback Frequency Avg -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200"><div class="flex items-center justify-between mb-3"><div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center"><i class="fas fa-users text-purple-600 text-lg"></i></div></div><div><p class="text-sm font-medium text-gray-600 mb-1">Feedback Freq. (Daily Avg)</p><p id="feedback-frequency" class="text-2xl font-bold text-gray-900">...</p></div></div>
                    <!-- Rating Distribution -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200"><div class="flex items-center justify-between mb-3"><div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center"><i class="fas fa-star text-jru-gold text-lg"></i></div><p id="rating-dist-total" class="text-xs text-gray-500">... total</p></div><div><p class="text-sm font-medium text-gray-600 mb-2">Rating Distribution</p><div id="rating-distribution-bars" class="space-y-1"></div></div></div>
                </div>
                
                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Service Performance -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200" id="service-performance-card">
                        <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold text-gray-900">Service Performance</h3></div>
                        <div id="service-performance-bars" class="space-y-4"> <!-- Populated by JS --> </div>
                    </div>
                    <!-- Sentiment Analysis (Placeholder) -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200"><div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold text-gray-900">Sentiment Analysis</h3></div><div class="chart-container"><canvas id="sentimentChart"></canvas></div><div class="grid grid-cols-3 gap-4 mt-4"><div class="text-center"><div class="flex items-center justify-center mb-1"><div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div><span class="text-sm text-gray-600">Positive</span></div><p class="text-lg font-bold text-gray-900">68%</p></div><div class="text-center"><div class="flex items-center justify-center mb-1"><div class="w-3 h-3 bg-gray-400 rounded-full mr-2"></div><span class="text-sm text-gray-600">Neutral</span></div><p class="text-lg font-bold text-gray-900">20%</p></div><div class="text-center"><div class="flex items-center justify-center mb-1"><div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div><span class="text-sm text-gray-600">Negative</span></div><p class="text-lg font-bold text-gray-900">12%</p></div></div></div>
                </div>
                
                <!-- Bottom Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Satisfaction Trends -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold text-gray-900">Satisfaction Trends</h3></div>
                        <div class="chart-container" id="trends-chart-container"><canvas id="trendsChart"></canvas></div>
                    </div>
                    <!-- Common Feedback (Placeholder) -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200"><h3 class="text-lg font-semibold text-gray-900 mb-4">Common Feedback</h3><div class="space-y-3"><div class="flex items-center justify-between p-3 bg-red-50 rounded-lg"><div class="flex items-center"><span class="text-sm font-medium">Slow WiFi</span></div><span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">23</span></div><div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg"><div class="flex items-center"><span class="text-sm font-medium">Limited Parking</span></div><span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">18</span></div><div class="flex items-center justify-between p-3 bg-green-50 rounded-lg"><div class="flex items-center"><span class="text-sm font-medium">Helpful Staff</span></div><span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">45</span></div><div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg"><div class="flex items-center"><span class="text-sm font-medium">Quick Service</span></div><span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">32</span></div></div></div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- CHART & ELEMENT REFERENCES ---
        let trendsChart; // To hold the chart instance for updates
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');

        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        // --- INITIALIZE CHARTS & SET DEFAULTS ---
        function initializeCharts() {
            // Placeholder for Sentiment Chart
            new Chart(document.getElementById('sentimentChart').getContext('2d'), {
                type: 'doughnut', data: { labels: ['Positive', 'Neutral', 'Negative'], datasets: [{ data: [68, 20, 12], backgroundColor: ['#10b981', '#9ca3af', '#ef4444'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }
            });

            // Initialize Trends Chart with empty data
            trendsChart = new Chart(trendsCtx, {
                type: 'line', data: { labels: [], datasets: [{ label: 'Satisfaction Score', data: [], borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', borderWidth: 3, fill: true, tension: 0.4, pointBackgroundColor: '#f59e0b', pointBorderColor: '#ffffff', pointBorderWidth: 2, pointRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false, min: 1, max: 5, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
            });
        }

        // --- DATA FETCHING & UI UPDATE LOGIC ---
        async function updateDashboard(startDate, endDate) {
            // Show loaders
            document.getElementById('metrics-grid').style.opacity = '0.5';
            document.getElementById('service-performance-card').style.opacity = '0.5';
            document.getElementById('trends-chart-container').classList.add('loading');

            try {
                const response = await fetch(`fetch_dashboard_data.php?startDate=${startDate}&endDate=${endDate}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                // Update UI with new data
                updateMetrics(data);
                updateServicePerformance(data.service_performance);
                updateRatingDistribution(data.rating_distribution, data.total_responses);
                updateTrendsChart(data.trends_labels, data.trends_data);

            } catch (error) {
                console.error('Failed to fetch dashboard data:', error);
                alert('Could not load dashboard data. Please check the console for details.');
            } finally {
                // Hide loaders
                document.getElementById('metrics-grid').style.opacity = '1';
                document.getElementById('service-performance-card').style.opacity = '1';
                 document.getElementById('trends-chart-container').classList.remove('loading');
            }
        }

        function updateMetrics(data) {
            document.getElementById('total-responses').textContent = data.total_responses.toLocaleString();
            document.getElementById('overall-satisfaction-score').textContent = data.overall_satisfaction;
            document.getElementById('feedback-frequency').textContent = data.feedback_frequency_avg.toLocaleString();
            
            const starsContainer = document.getElementById('overall-satisfaction-stars');
            starsContainer.innerHTML = '';
            const roundedStars = Math.round(data.overall_satisfaction);
            for(let i=1; i<=5; i++) {
                const iconClass = i <= roundedStars ? 'fas fa-star' : 'far fa-star';
                starsContainer.innerHTML += `<i class="${iconClass} text-xs"></i>`;
            }
        }

        function updateServicePerformance(performanceData) {
            const container = document.getElementById('service-performance-bars');
            container.innerHTML = ''; // Clear previous bars
            const performanceItems = [
                { label: 'Service Quality', value: performanceData.service_quality, color: 'bg-green-500' },
                { label: 'Response Time', value: performanceData.response_time, color: 'bg-blue-500' },
                { label: 'Staff Courtesy', value: performanceData.staff_courtesy, color: 'bg-jru-gold' },
                { label: 'Process Efficiency', value: performanceData.process_efficiency, color: 'bg-yellow-500' }
            ];

            performanceItems.forEach(item => {
                const percentage = (item.value / 5) * 100;
                const html = `
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">${item.label}</span>
                            <span class="font-medium">${item.value}/5.0</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="${item.color} h-2 rounded-full" style="width: ${percentage}%"></div>
                        </div>
                    </div>`;
                container.innerHTML += html;
            });
        }
        
        function updateRatingDistribution(distribution, total) {
            document.getElementById('rating-dist-total').textContent = `${total.toLocaleString()} total`;
            const container = document.getElementById('rating-distribution-bars');
            container.innerHTML = '';
            const colors = { 5: 'bg-green-500', 4: 'bg-blue-500', 3: 'bg-yellow-500', 2: 'bg-orange-500', 1: 'bg-red-500' };

            for (let i = 5; i >= 1; i--) {
                const count = distribution[i] || 0;
                const percentage = total > 0 ? (count / total) * 100 : 0;
                 const html = `
                    <div class="flex items-center text-xs">
                        <span class="w-3 text-gray-600">${i}</span>
                        <div class="flex-1 mx-2">
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="${colors[i]} h-1.5 rounded-full" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                        <span class="w-8 text-right text-gray-700">${count.toLocaleString()}</span>
                    </div>`;
                container.innerHTML += html;
            }
        }

        function updateTrendsChart(labels, data) {
            trendsChart.data.labels = labels;
            trendsChart.data.datasets[0].data = data;
            trendsChart.update();
        }

        // --- EVENT LISTENERS ---
        function handleDateChange() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            if (startDate && endDate && startDate <= endDate) {
                updateDashboard(startDate, endDate);
            }
        }

        startDateInput.addEventListener('change', handleDateChange);
        endDateInput.addEventListener('change', handleDateChange);
        
        // --- INITIAL LOAD ---
        // Set default dates and load initial data
        endDateInput.value = new Date().toISOString().split('T')[0];
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 6);
        startDateInput.value = sevenDaysAgo.toISOString().split('T')[0];
        
        initializeCharts();
        handleDateChange(); // Trigger initial data load
        
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('sidebar-expanded');
            sidebar.classList.toggle('sidebar-collapsed');
            // Simplified toggle logic
        });
    });
    </script>
</body>
</html>
