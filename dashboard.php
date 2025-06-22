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

// --- Fetch unique office names for the export filter ---
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "db_jru_pulse";
$conn = new mysqli($servername, $username, $password, $dbname);
$offices = [];

if ($conn->connect_error) {
    // Handle error gracefully, maybe log it
    // For now, we'll let the array be empty
} else {
    $result = $conn->query("SELECT DISTINCT office FROM tbl_feedback_submissions ORDER BY office ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $offices[] = $row['office'];
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JRU-PULSE Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link href="dist/output.css" rel="stylesheet">

    <style>
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

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .sidebar-transition { transition: all .3s cubic-bezier(.4, 0, .2, 1); }
        .chart-container { position: relative; height: 220px; width: 100%; }
        @media (max-width: 768px) { .chart-container { height: 180px; } }
        .sidebar-collapsed { width: 5rem; }
        .sidebar-expanded { width: 16rem; }
        .menu-text { transition: opacity .2s ease-in-out; }
        .logo-transition { transition: all .3s ease; }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }
        
        .filter-btn { background-color: #f3f4f6; color: #4b5563; transition: background-color 0.2s, color 0.2s, box-shadow 0.2s; border: 1px solid #d1d5db; }
        .filter-btn.active { background-color: #1e3a8a; color: #ffffff; font-weight: 600; border-color: #1e3a8a; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
        .filter-btn:hover:not(.active) { background-color: #e5e7eb; }
        
        .modal-overlay { transition: opacity 0.3s ease; }
        #notification-dropdown { display: none; }
        .notification-item:hover { background-color: #f3f4f6; }
    </style>
</head>

<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-transition sidebar-expanded bg-blue-950 shadow-lg flex flex-col border-r border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="p-2 rounded-lg hover:bg-gray-600 transition-colors mr-3">
                        <i class="fas fa-bars text-gray-100"></i>
                    </button>
                    <div id="logoContainer" class="logo-transition flex items-center">
                        <img src="dist/assets/jru-pulse-final-white.png" alt="JRU-PULSE" class="h-8 w-auto">
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <li><a href="dashboard.php" class="flex items-center px-3 py-3 bg-blue-50 text-jru-blue rounded-lg font-medium"><i class="fas fa-tachometer-alt text-lg w-6"></i><span class="menu-text ml-3">Dashboard</span></a></li>
                    <li><a href="survey-management.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-poll text-lg w-6"></i><span class="menu-text ml-3">Survey Management</span></a></li>
                    <li><a href="performance-analytics-reports.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-chart-line text-lg w-6"></i><span class="menu-text ml-3">Performance Analytics & Reports</span></a></li>
                    <li><a href="user-management.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-users text-lg w-6"></i><span class="menu-text ml-3">User Management</span></a></li>
                    <li><a href="#" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-cog text-lg w-6"></i><span class="menu-text ml-3">Settings</span></a></li>
                </ul>
                <br>
                <div class="mt-8">
                    <div id="quickActionsHeader" class="menu-text text-xs font-semibold text-gray-50 uppercase tracking-wider mb-3">Quick Actions</div>
                    <div class="space-y-2">
                        <button class="flex items-center w-full px-3 py-2 text-sm text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-plus text-sm w-6"></i><span class="menu-text ml-3">New Survey</span></button>
                        <button id="open-export-modal-btn" class="flex items-center w-full px-3 py-2 text-sm text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-download text-sm w-6"></i><span class="menu-text ml-3">Export Data</span></button>
                    </div>
                </div>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <?php if (isset($user['picture']) && !empty($user['picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="User Picture" class="w-10 h-10 rounded-full object-cover mr-3">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-gradient-to-r from-jru-gold to-yellow-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-3">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div id="userInfo" class="menu-text ml-0 flex-1 overflow-hidden">
                        <p class="text-sm font-medium truncate text-white" title="<?php echo htmlspecialchars($user['name']); ?>"><?php echo htmlspecialchars($user['name']); ?></p>
                        <p class="text-xs text-gray-400 truncate" title="<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <a href="logout.php" class="menu-text p-2 text-gray-50 hover:text-yellow-400 transition-colors"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
                        <p class="text-sm text-gray-600 mt-1">Performance and User-satisfaction Linked Services Evaluation</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative" id="notification-bell-container">
                            <button id="notification-bell" class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full transition-colors">
                                <i class="fas fa-bell text-xl"></i>
                                <span id="notification-count" class="hidden absolute -top-1 -right-1 w-5 h-5 text-xs bg-red-500 text-white rounded-full flex items-center justify-center"></span>
                            </button>
                            <div id="notification-dropdown" class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-20">
                                <div class="p-3 border-b font-semibold text-gray-800">Notifications</div>
                                <div id="notification-list" class="max-h-96 overflow-y-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <!-- Filters -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6 space-y-4 lg:space-y-0">
                    <div id="preset-filters" class="flex items-center space-x-2 flex-wrap">
                        <button data-period="this_week" class="filter-btn px-3 py-1 text-sm rounded-full mb-2">This Week</button>
                        <button data-period="this_month" class="filter-btn px-3 py-1 text-sm rounded-full mb-2">This Month</button>
                        <button data-period="this_quarter" class="filter-btn px-3 py-1 text-sm rounded-full mb-2">This Quarter</button>
                        <button data-period="this_year" class="filter-btn px-3 py-1 text-sm rounded-full mb-2">This Year</button>
                        <button data-period="all_time" class="filter-btn px-3 py-1 text-sm rounded-full mb-2">All Time</button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600 font-medium">Custom Range:</span>
                        <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue w-36">
                        <span class="text-gray-400">to</span>
                        <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue w-36">
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div id="metrics-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Overall Satisfaction -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-smile text-jru-blue text-lg"></i></div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Overall Satisfaction</p>
                            <div class="flex items-center">
                                <span id="overall-satisfaction-score" class="text-2xl font-bold text-gray-900 mr-2">...</span>
                                <div id="overall-satisfaction-stars" class="flex text-jru-gold"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Responses -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"><i class="fas fa-chart-bar text-green-600 text-lg"></i></div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Responses</p>
                            <p id="total-responses" class="text-2xl font-bold text-gray-900">...</p>
                        </div>
                    </div>
                    <!-- Feedback Frequency -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center"><i class="fas fa-users text-purple-600 text-lg"></i></div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Feedback Freq. (Daily Avg)</p>
                            <p id="feedback-frequency" class="text-2xl font-bold text-gray-900">...</p>
                        </div>
                    </div>
                    <!-- Rating Distribution -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center"><i class="fas fa-star text-jru-gold text-lg"></i></div>
                            <p id="rating-dist-total" class="text-xs text-gray-500">... total</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-2">Rating Distribution</p>
                            <div id="rating-distribution-bars" class="space-y-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Data Sections -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Service Performance -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200" id="service-performance-card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Service Performance</h3>
                        </div>
                        <div id="service-performance-bars" class="space-y-4"></div>
                    </div>
                    <!-- Sentiment Analysis -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Sentiment Analysis</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="sentimentChart"></canvas>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-1">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div><span class="text-sm text-gray-600">Positive</span>
                                </div>
                                <p class="text-lg font-bold text-gray-900">68%</p>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-1">
                                    <div class="w-3 h-3 bg-gray-400 rounded-full mr-2"></div><span class="text-sm text-gray-600">Neutral</span>
                                </div>
                                <p class="text-lg font-bold text-gray-900">20%</p>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-1">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div><span class="text-sm text-gray-600">Negative</span>
                                </div>
                                <p class="text-lg font-bold text-gray-900">12%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Satisfaction Trends -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Satisfaction Trends</h3>
                            <button id="open-trends-modal-btn" class="text-sm text-jru-blue hover:text-blue-800 font-medium"><i class="fas fa-expand-alt mr-1"></i>View Larger</button>
                        </div>
                        <div class="chart-container" id="trends-chart-container">
                            <canvas id="trendsChart"></canvas>
                        </div>
                    </div>
                    <!-- Common Feedback -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Common Feedback</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                <div class="flex items-center"><span class="text-sm font-medium">Slow WiFi</span></div><span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">23</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                <div class="flex items-center"><span class="text-sm font-medium">Limited Parking</span></div><span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">18</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center"><span class="text-sm font-medium">Helpful Staff</span></div><span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">45</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center"><span class="text-sm font-medium">Quick Service</span></div><span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">32</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Trends Chart Modal -->
    <div id="trends-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-full flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-xl font-semibold text-gray-900">Satisfaction Trends</h3>
                <button id="close-trends-modal-btn" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-2xl"></i></button>
            </div>
            <div class="p-6 flex-grow">
                <div class="h-full w-full">
                    <canvas id="modalTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Details Modal -->
    <div id="feedback-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
            <div id="feedback-modal-header" class="flex items-center justify-between p-4 border-b">
                <h3 id="feedback-modal-title" class="text-xl font-semibold text-gray-900">Feedback Details</h3>
                <button id="close-feedback-modal-btn" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-2xl"></i></button>
            </div>
            <div id="feedback-modal-body" class="p-6 space-y-4"></div>
        </div>
    </div>

    <!-- Export Data Modal -->
    <div id="export-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
            <form id="export-form">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Export Feedback Data</h3>
                    <button type="button" id="close-export-modal-btn" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-2xl"></i></button>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <div class="flex items-center space-x-2">
                            <input type="date" id="export-startDate" required class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue w-full">
                            <span class="text-gray-400">to</span>
                            <input type="date" id="export-endDate" required class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue w-full">
                        </div>
                    </div>
                    <div>
                        <label for="export-office" class="block text-sm font-medium text-gray-700">Filter by Office</label>
                        <select id="export-office" class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-jru-blue focus:border-jru-blue sm:text-sm">
                            <option value="all">All Offices</option>
                            <?php foreach ($offices as $office_name): ?>
                                <option value="<?php echo htmlspecialchars($office_name); ?>"><?php echo htmlspecialchars($office_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Columns to Include</label>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                            <label class="flex items-center"><input type="checkbox" name="columns" value="student_no" checked class="h-4 w-4 rounded border-gray-300 text-jru-blue focus:ring-jru-blue"> <span class="ml-2">Student No</span></label>
                            <label class="flex items-center"><input type="checkbox" name="columns" value="division" checked class="h-4 w-4 rounded border-gray-300 text-jru-blue focus:ring-jru-blue"> <span class="ml-2">Division</span></label>
                            <label class="flex items-center"><input type="checkbox" name="columns" value="office" checked class="h-4 w-4 rounded border-gray-300 text-jru-blue focus:ring-jru-blue"> <span class="ml-2">Office</span></label>
                            <label class="flex items-center"><input type="checkbox" name="columns" value="service" checked class="h-4 w-4 rounded border-gray-300 text-jru-blue focus:ring-jru-blue"> <span class="ml-2">Service</span></label>
                            <label class="flex items-center"><input type="checkbox" name="columns" value="service_outcome" checked class="h-4 w-4 rounded border-gray-300 text-jru-blue focus:ring-jru-blue"> <span class="ml-2">Ratings</span></label>
                            <label class="flex items-center"><input type="checkbox" name="columns" value="suggestions" checked class="h-4 w-4 rounded border-gray-300 text-jru-blue focus:ring-jru-blue"> <span class="ml-2">Suggestions</span></label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">File Format</label>
                        <div class="mt-2 flex space-x-4">
                            <label class="flex items-center"><input type="radio" name="format" value="xlsx" checked class="h-4 w-4 text-jru-blue focus:ring-jru-blue border-gray-300"><span class="ml-2">Excel (.xlsx)</span></label>
                            <label class="flex items-center"><input type="radio" name="format" value="csv" class="h-4 w-4 text-jru-blue focus:ring-jru-blue border-gray-300"><span class="ml-2">CSV</span></label>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button type="button" id="cancel-export-btn" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-jru-blue hover:bg-jru-navy">Export Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- GLOBAL REFERENCES ---
            let trendsChart, modalTrendsChart;
            const trendsCtx = document.getElementById('trendsChart').getContext('2d');
            const modalTrendsCtx = document.getElementById('modalTrendsChart').getContext('2d');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const presetFiltersContainer = document.getElementById('preset-filters');
            const trendsModal = document.getElementById('trends-modal');
            const openModalBtn = document.getElementById('open-trends-modal-btn');
            const closeModalBtn = document.getElementById('close-trends-modal-btn');
            const notificationBell = document.getElementById('notification-bell');
            const notificationDropdown = document.getElementById('notification-dropdown');
            const notificationCount = document.getElementById('notification-count');
            const notificationList = document.getElementById('notification-list');
            const feedbackModal = document.getElementById('feedback-modal');
            const feedbackModalTitle = document.getElementById('feedback-modal-title');
            const feedbackModalBody = document.getElementById('feedback-modal-body');
            const closeFeedbackModalBtn = document.getElementById('close-feedback-modal-btn');

            // --- SCRIPT FOR THE MAIN DASHBOARD ---

            function createChartConfig() {
                return {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Satisfaction Score',
                            data: [],
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#f59e0b',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: false, min: 1, max: 5, grid: { color: '#f3f4f6' } },
                            x: { grid: { display: false } }
                        }
                    }
                };
            }

            function initializeCharts() {
                new Chart(document.getElementById('sentimentChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Positive', 'Neutral', 'Negative'],
                        datasets: [{
                            data: [68, 20, 12],
                            backgroundColor: ['#10b981', '#9ca3af', '#ef4444'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        cutout: '70%'
                    }
                });
                trendsChart = new Chart(trendsCtx, createChartConfig());
                modalTrendsChart = new Chart(modalTrendsCtx, createChartConfig());
            }

            async function updateDashboard(params) {
                document.getElementById('metrics-grid').style.opacity = '0.5';
                document.getElementById('service-performance-card').style.opacity = '0.5';
                document.getElementById('trends-chart-container').classList.add('loading');

                const queryParams = new URLSearchParams();
                if (params.period) {
                    queryParams.append('period', params.period);
                } else if (params.startDate && params.endDate) {
                    queryParams.append('startDate', params.startDate);
                    queryParams.append('endDate', params.endDate);
                }
                const url = `fetch_dashboard_data.php?${queryParams.toString()}`;

                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);
                    updateUI(data);
                } catch (error) {
                    console.error('Failed to fetch dashboard data:', error);
                    alert('Could not load dashboard data. ' + error.message);
                } finally {
                    document.getElementById('metrics-grid').style.opacity = '1';
                    document.getElementById('service-performance-card').style.opacity = '1';
                    document.getElementById('trends-chart-container').classList.remove('loading');
                }
            }

            function updateUI(data) {
                startDateInput.value = data.startDate;
                endDateInput.value = data.endDate;
                document.getElementById('total-responses').textContent = data.total_responses.toLocaleString();
                document.getElementById('overall-satisfaction-score').textContent = data.overall_satisfaction;
                document.getElementById('feedback-frequency').textContent = data.feedback_frequency_avg.toLocaleString();

                const starsContainer = document.getElementById('overall-satisfaction-stars');
                starsContainer.innerHTML = '';
                const roundedStars = Math.round(data.overall_satisfaction);
                for (let i = 1; i <= 5; i++) {
                    starsContainer.innerHTML += `<i class="${i <= roundedStars ? 'fas fa-star' : 'far fa-star'} text-xs"></i>`;
                }

                const performanceContainer = document.getElementById('service-performance-bars');
                performanceContainer.innerHTML = '';
                const performanceItems = [
                    { label: 'Service Quality', value: data.service_performance.service_quality, color: 'bg-green-500' },
                    { label: 'Response Time', value: data.service_performance.response_time, color: 'bg-blue-500' },
                    { label: 'Staff Courtesy', value: data.service_performance.staff_courtesy, color: 'bg-jru-gold' },
                    { label: 'Process Efficiency', value: data.service_performance.process_efficiency, color: 'bg-yellow-500' }
                ];
                performanceItems.forEach(item => {
                    const percentage = (item.value / 5) * 100;
                    performanceContainer.innerHTML += `<div><div class="flex justify-between text-sm mb-2"><span class="text-gray-600">${item.label}</span><span class="font-medium">${item.value}/5.0</span></div><div class="w-full bg-gray-200 rounded-full h-2"><div class="${item.color} h-2 rounded-full" style="width: ${percentage}%"></div></div></div>`;
                });

                document.getElementById('rating-dist-total').textContent = `${data.total_responses.toLocaleString()} total`;
                const ratingContainer = document.getElementById('rating-distribution-bars');
                ratingContainer.innerHTML = '';
                const ratingColors = { 5: 'bg-green-500', 4: 'bg-blue-500', 3: 'bg-yellow-500', 2: 'bg-orange-500', 1: 'bg-red-500' };
                for (let i = 5; i >= 1; i--) {
                    const count = data.rating_distribution[i] || 0;
                    const percentage = data.total_responses > 0 ? (count / data.total_responses) * 100 : 0;
                    ratingContainer.innerHTML += `<div class="flex items-center text-xs"><span class="w-3 text-gray-600">${i}</span><div class="flex-1 mx-2"><div class="w-full bg-gray-200 rounded-full h-1.5"><div class="${ratingColors[i]} h-1.5 rounded-full" style="width: ${percentage}%"></div></div></div><span class="w-8 text-right text-gray-700">${count.toLocaleString()}</span></div>`;
                }

                [trendsChart, modalTrendsChart].forEach(chart => {
                    chart.data.labels = data.trends_labels;
                    chart.data.datasets[0].data = data.trends_data;
                    chart.update();
                });
            }

            async function fetchNotifications() {
                try {
                    const response = await fetch('api/fetch_notifications.php?action=fetch');
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);
                    updateNotificationUI(data.notifications);
                } catch (error) {
                    console.error("Error fetching notifications:", error);
                }
            }

            function updateNotificationUI(notifications) {
                notificationList.innerHTML = '';
                if (notifications.length > 0) {
                    notificationCount.textContent = notifications.length;
                    notificationCount.classList.remove('hidden');
                    notifications.forEach(notif => {
                        const item = document.createElement('div');
                        item.className = 'notification-item p-3 border-b border-gray-100 cursor-pointer flex items-start space-x-3';
                        item.dataset.id = notif.id;
                        item.dataset.type = notif.type;
                        item.dataset.details = JSON.stringify(notif);

                        let iconHtml, titleHtml, bodyHtml, timeHtml;
                        if (notif.type === 'critical') {
                            iconHtml = `<i class="fas fa-exclamation-circle text-red-500 text-xl mt-1"></i>`;
                            titleHtml = `<p class="font-bold text-sm text-red-600">Low Rating Alert: ${parseFloat(notif.avg_rating).toFixed(1)}/5.0</p>`;
                            bodyHtml = `<p class="text-sm text-gray-700 truncate">For ${notif.office} - ${notif.service}</p>`;
                            timeHtml = `<p class="text-xs text-gray-500">${new Date(notif.date_submitted + ' ' + notif.time_submitted).toLocaleString()}</p>`;
                        } else {
                            iconHtml = `<i class="fas fa-arrow-trend-down text-yellow-500 text-xl mt-1"></i>`;
                            titleHtml = `<p class="font-bold text-sm text-yellow-600">Performance Dip Alert</p>`;
                            bodyHtml = `<p class="text-sm text-gray-700 truncate">${notif.service} dropped to ${parseFloat(notif.avg_rating_this_week).toFixed(1)}/5.0</p>`;
                            timeHtml = `<p class="text-xs text-gray-500">${new Date(notif.alert_date).toLocaleDateString()}</p>`;
                        }
                        item.innerHTML = `${iconHtml}<div>${titleHtml}${bodyHtml}${timeHtml}</div>`;
                        notificationList.appendChild(item);
                    });
                } else {
                    notificationCount.classList.add('hidden');
                    notificationList.innerHTML = '<p class="p-4 text-sm text-gray-500 text-center">No new alerts.</p>';
                }
            }

            async function markNotificationAsRead(id, type) {
                try {
                    await fetch(`api/fetch_notifications.php?action=mark_as_read&id=${id}&type=${type}`);
                    fetchNotifications();
                } catch (error) {
                    console.error("Failed to mark notification as read:", error);
                }
            }

            function handleDateChange() {
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                if (startDate && endDate && startDate <= endDate) {
                    updateDashboard({ startDate: startDate, endDate: endDate });
                }
            }
            
            // --- Event Listeners ---
            presetFiltersContainer.addEventListener('click', e => {
                if (e.target.tagName === 'BUTTON') {
                    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                    updateDashboard({ period: e.target.dataset.period });
                }
            });

            startDateInput.addEventListener('change', handleDateChange);
            endDateInput.addEventListener('change', handleDateChange);

            openModalBtn.addEventListener('click', () => trendsModal.classList.remove('hidden'));
            closeModalBtn.addEventListener('click', () => trendsModal.classList.add('hidden'));
            trendsModal.addEventListener('click', e => { if (e.target === trendsModal) trendsModal.classList.add('hidden') });

            closeFeedbackModalBtn.addEventListener('click', () => feedbackModal.classList.add('hidden'));
            feedbackModal.addEventListener('click', e => { if (e.target === feedbackModal) feedbackModal.classList.add('hidden') });

            notificationBell.addEventListener('click', e => {
                e.stopPropagation();
                notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
            });
            document.addEventListener('click', () => { notificationDropdown.style.display = 'none'; });
            notificationDropdown.addEventListener('click', e => e.stopPropagation());

            notificationList.addEventListener('click', e => {
                const item = e.target.closest('.notification-item');
                if (item) {
                    const details = JSON.parse(item.dataset.details);
                    const type = item.dataset.type;
                    let modalBodyContent = '';
                    if (type === 'critical') {
                        feedbackModalTitle.textContent = 'Critical Alert Details';
                        modalBodyContent = `<p><strong>Student Number:</strong> ${details.student_no || 'N/A'}</p><p><strong>Office:</strong> ${details.office}</p><p><strong>Service:</strong> ${details.service}</p><p><strong>Date:</strong> ${new Date(details.date_submitted).toLocaleDateString()}</p><p><strong>Average Rating:</strong> <span class="font-bold text-red-600">${parseFloat(details.avg_rating).toFixed(1)}/5.0</span></p><div class="mt-2 p-3 bg-gray-50 rounded-lg"><p class="font-semibold">Suggestions:</p><p class="text-gray-700">${details.suggestions || 'No suggestion provided.'}</p></div>`;
                    } else {
                        feedbackModalTitle.textContent = 'Performance Dip Details';
                        modalBodyContent = `<p><strong>Office:</strong> ${details.office}</p><p><strong>Service:</strong> ${details.service}</p><p>A performance dip was detected for this service on <strong>${new Date(details.alert_date).toLocaleDateString()}</strong>.</p><div class="mt-4 flex space-x-4 text-center"><div class="flex-1 p-3 bg-red-50 rounded-lg"><p class="text-sm text-red-700">This Week's Avg</p><p class="text-2xl font-bold text-red-600">${parseFloat(details.avg_rating_this_week).toFixed(1)}</p></div><div class="flex-1 p-3 bg-gray-100 rounded-lg"><p class="text-sm text-gray-600">Last Week's Avg</p><p class="text-2xl font-bold text-gray-800">${parseFloat(details.avg_rating_last_week).toFixed(1)}</p></div></div>`;
                    }
                    feedbackModalBody.innerHTML = modalBodyContent;
                    feedbackModal.classList.remove('hidden');
                    notificationDropdown.style.display = 'none';
                    markNotificationAsRead(details.id, type);
                }
            });

            // --- Initial Load ---
            initializeCharts();
            document.querySelector('.filter-btn[data-period="this_week"]').classList.add('active');
            updateDashboard({ period: 'this_week' });
            fetchNotifications();
            setInterval(fetchNotifications, 30000); // Poll for new notifications every 30 seconds

            // --- NEW SCRIPT FOR EXPORT MODAL ---
            const exportModal = document.getElementById('export-modal');
            const openExportModalBtn = document.getElementById('open-export-modal-btn');
            const closeExportModalBtn = document.getElementById('close-export-modal-btn');
            const cancelExportBtn = document.getElementById('cancel-export-btn');
            const exportForm = document.getElementById('export-form');

            function initializeExportModal() {
                // Set default dates to match the main dashboard's current view
                document.getElementById('export-startDate').value = startDateInput.value;
                document.getElementById('export-endDate').value = endDateInput.value;
            }

            openExportModalBtn.addEventListener('click', () => {
                initializeExportModal();
                exportModal.classList.remove('hidden');
            });

            closeExportModalBtn.addEventListener('click', () => exportModal.classList.add('hidden'));
            cancelExportBtn.addEventListener('click', () => exportModal.classList.add('hidden'));

            exportForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const startDate = document.getElementById('export-startDate').value;
                const endDate = document.getElementById('export-endDate').value;
                const office = document.getElementById('export-office').value;
                const format = exportForm.querySelector('input[name="format"]:checked').value;

                const selectedColumns = Array.from(exportForm.querySelectorAll('input[name="columns"]:checked'))
                    .map(cb => cb.value);

                // Special case for ratings: 'service_outcome' checkbox controls all rating columns
                if (selectedColumns.includes('service_outcome')) {
                    const ratingCols = ['speed_of_service', 'processing_instruction', 'staff_professionalism_courtesy'];
                    // Add other rating columns if not already in the list
                    ratingCols.forEach(col => {
                        if (!selectedColumns.includes(col)) selectedColumns.push(col);
                    });
                }

                if (selectedColumns.length === 0) {
                    alert('Please select at least one column to export.');
                    return;
                }

                const params = new URLSearchParams({
                    startDate,
                    endDate,
                    office,
                    format,
                    columns: selectedColumns.join(',')
                });

                // Trigger the download by navigating to the export API URL
                window.location.href = `api/export_api.php?${params.toString()}`;

                exportModal.classList.add('hidden');
            });
        });
    </script>
</body>

</html>