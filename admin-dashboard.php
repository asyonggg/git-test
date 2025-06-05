<?php
session_start(); // Must be at the top

// Check if the user is logged in and is an admin
// $_SESSION['user_data'] is set by callback.php
if (!isset($_SESSION['user_data']) || $_SESSION['user_data']['role'] !== 'admin') {
    // Not logged in or not an admin, redirect to login page (e.g., index.html)
    header('Location: index.html?error=auth_required'); // Add an error query for clarity
    exit;
}

// Get user data from session
$user = $_SESSION['user_data']; // This now contains 'email', 'name', 'picture' (optional), 'role'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JRU-A-PULSE Admin Dashboard</title> 
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
     <link rel="stylesheet" href="styles.css">  

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jru-blue': '#1e3a8a', // Main JRU Blue
                        'jru-gold': '#f59e0b', // JRU Gold/Orange
                        'jru-light-blue': '#3b82f6', // A lighter blue for accents
                        'jru-dark-blue': '#1e3a8a', // A darker blue (can be same as main)
                        'sidebar-bg': '#0c2f5a', // Darker blue for sidebar
                        'sidebar-hover': '#1e4c8a', // Hover color for sidebar items
                        'sidebar-text': '#e0e7ff', // Light text for sidebar
                        'sidebar-active-bg': '#3b82f6', // Active item background
                        'sidebar-active-text': '#ffffff', // Active item text
                    }
                }
            }
        }
    </script>
    <style>
        /* Dasboard Styles */
        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .chart-container {
            position: relative;
            height: 220px; /* Adjusted for better fit */
            width: 100%;
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 180px;
            }
        }

        .sidebar-collapsed {
            width: 5rem; /* 80px */
        }

        .sidebar-expanded {
            width: 16rem; /* 256px */
        }

        .menu-text {
            transition: opacity 0.2s ease-in-out, display 0.2s ease-in-out;
        }
        .sidebar-collapsed .menu-text {
            opacity: 0;
            pointer-events: none; /* Prevents interaction when hidden */
        }
        .sidebar-expanded .menu-text {
            opacity: 1;
        }


        .logo-transition {
            transition: all 0.3s ease;
        }
         .sidebar-collapsed #logoContainer img {
            /* Optionally hide or shrink logo when collapsed */
            /* display: none; */
         }
         .sidebar-expanded #logoContainer img {
            display: block;
         }


        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #edf2f7; /* Lighter gray */
        }
        ::-webkit-scrollbar-thumb {
            background: #a0aec0; /* Medium gray */
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #718096; /* Darker gray */
        }

        body {
            font-family: 'Inter', sans-serif; /* A common modern sans-serif font */
        }
        /* Apply Tailwind's bg-sidebar-bg */
        #sidebar {
             background-color: tailwind.config.theme.extend.colors['sidebar-bg'];
        }
    </style>
   
</head>
<body class="bg-gray-100 font-sans"> 
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-transition sidebar-expanded bg-sidebar-bg shadow-lg flex flex-col border-r border-gray-700"> 
            <!-- Logo Section -->
            <div class="p-4 border-b border-gray-700 flex items-center justify-between">
                <div id="logoContainer" class="logo-transition flex items-center overflow-hidden">

                    <img src="assets/JRU-PULSE-final-white.png" alt="JRU-A-PULSE" class="h-8 w-auto mr-2">
                </div>
                <button id="sidebarToggle" class="p-2 rounded-lg text-sidebar-text hover:bg-sidebar-hover transition-colors">
                    <i class="fas fa-bars text-xl "></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
               
                <a href="admin-dashboard.php" class="flex items-center px-3 py-3 rounded-lg font-medium bg-sidebar-active-bg text-sidebar-active-text">
                    <i class="fas fa-tachometer-alt text-lg w-6"></i>
                    <span class="menu-text ml-3">Dashboard</span>
                </a>
                <a href="survey-management.php" class="flex items-center px-3 py-3 text-sidebar-text hover:bg-sidebar-hover hover:text-white rounded-lg transition-colors">
                    <i class="fas fa-poll text-lg w-6"></i>
                    <span class="menu-text ml-3">Survey Management</span>
                </a>
                <a href="performance-analytics-reports.php" class="flex items-center px-3 py-3 text-sidebar-text hover:bg-sidebar-hover hover:text-white rounded-lg transition-colors">
                    <i class="fas fa-chart-line text-lg w-6"></i>
                    <span class="menu-text ml-3">Analytics & Reports</span>
                </a>
               
                <a href="#" class="flex items-center px-3 py-3 text-sidebar-text hover:bg-sidebar-hover hover:text-white rounded-lg transition-colors">
                    <i class="fas fa-users text-lg w-6"></i>
                    <span class="menu-text ml-3">User Management</span>
                </a>
                <a href="#" class="flex items-center px-3 py-3 text-sidebar-text hover:bg-sidebar-hover hover:text-white rounded-lg transition-colors">
                    <i class="fas fa-cog text-lg w-6"></i>
                    <span class="menu-text ml-3">Settings</span>
                </a>
                <hr class="my-4 border-gray-700 menu-text">
                <!-- Quick Actions -->
                <div class="menu-text">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                        Quick Actions
                    </div>
                    <div class="space-y-2">
                        <button class="flex items-center w-full px-3 py-2 text-sm text-sidebar-text hover:bg-sidebar-hover hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-plus text-sm w-6"></i>
                            <span class="menu-text ml-3">New Survey</span>
                        </button>
                        <button class="flex items-center w-full px-3 py-2 text-sm text-sidebar-text hover:bg-sidebar-hover hover:text-white rounded-lg transition-colors">
                            <i class="fas fa-download text-sm w-6"></i>
                            <span class="menu-text ml-3">Export Data</span>
                        </button>
                    </div>
                </div>
            </nav>
            
            <!-- User Profile -->
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <?php if (isset($user['picture']) && !empty($user['picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="User Picture" class="w-10 h-10 rounded-full object-cover mr-3">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-gradient-to-r from-jru-gold to-yellow-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-3">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); // Display first initial ?>
                        </div>
                    <?php endif; ?>
                    <div id="userInfo" class="menu-text ml-0 flex-1 overflow-hidden"> 
                        <p class="text-sm font-medium text-sidebar-text truncate" title="<?php echo htmlspecialchars($user['name']); ?>"><?php echo htmlspecialchars($user['name']); ?></p>
                        <p class="text-xs text-gray-400 truncate" title="<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                 
                    <a href="logout.php" title="Logout" class="menu-text p-2 text-gray-400 hover:text-jru-gold transition-colors">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1> 
                        <p class="text-sm text-gray-500 mt-1">Performance and User-satisfaction Linked Services Evaluation</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="relative hidden md:block"> 
                            <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 w-64 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue focus:border-transparent transition-all">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        
                        <!-- Notifications -->
                        <button class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></span> 
                        </button>
                         <!-- User menu for mobile or if picture is preferred in header -->
                        <div class="md:hidden"> 
                             <?php if (isset($user['picture']) && !empty($user['picture'])): ?>
                                <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="User" class="w-8 h-8 rounded-full">
                            <?php else: ?>
                                <div class="w-8 h-8 bg-jru-blue text-white flex items-center justify-center rounded-full text-sm font-semibold">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Welcome Message -->
                <div class="mb-6 p-4 bg-jru-blue text-white rounded-lg shadow">
                    <h2 class="text-xl font-semibold">Welcome back, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>!</h2> 
                    <p class="text-sm opacity-90">Here's an overview of the system's pulse.</p>
                </div>

                <!-- Controls -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6 space-y-4 lg:space-y-0">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue bg-white">
                            <option>This Week</option>
                            <option>This Month</option>
                            <option>This Quarter</option>
                            <option>This Year</option>
                        </select>
                        <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue bg-white">
                            <option>All Offices</option>
                            <option>Registrar</option>
                            <option>Cashier</option>
                            <option>Library</option>
                            <option>IT Services</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Date Range:</span>
                        <input type="date" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                        <span class="text-gray-400">to</span>
                        <input type="date" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                    </div>
                </div>
                
                <!-- Key Metrics -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Overall Satisfaction -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-smile text-jru-blue text-lg"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-green-600">
                                    <i class="fas fa-arrow-up mr-1"></i>+0.3
                                </p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Overall Satisfaction</p>
                            <div class="flex items-center">
                                <span class="text-2xl font-bold text-gray-900 mr-2">4.2</span>
                                <div class="flex text-jru-gold">
                                    <i class="fas fa-star text-xs"></i>
                                    <i class="fas fa-star text-xs"></i>
                                    <i class="fas fa-star text-xs"></i>
                                    <i class="fas fa-star text-xs"></i>
                                    <i class="far fa-star text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Responses -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-bar text-green-600 text-lg"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-green-600">
                                    <i class="fas fa-arrow-up mr-1"></i>+12%
                                </p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Responses</p>
                            <p class="text-2xl font-bold text-gray-900">1,247</p>
                        </div>
                    </div>
                    
                    <!-- Feedback Frequency Avg -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-purple-600 text-lg"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-green-600">
                                    <i class="fas fa-arrow-up mr-1"></i>+8%
                                </p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Feedback Frequency Avg</p>
                            <p class="text-2xl font-bold text-gray-900">178</p>
                        </div>
                    </div>
                    
                    <!-- Rating Distribution -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-star text-jru-gold text-lg"></i>
                            </div>
                            <p class="text-xs text-gray-500">1,247 total</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-2">Rating Distribution</p>
                            <!-- Compact Rating Bars -->
                            <div class="space-y-1">
                                <div class="flex items-center text-xs">
                                    <span class="w-3 text-gray-600">5</span>
                                    <div class="flex-1 mx-2">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-green-500 h-1.5 rounded-full" style="width: 45%"></div>
                                        </div>
                                    </div>
                                    <span class="w-8 text-right text-gray-700">562</span>
                                </div>
                                <div class="flex items-center text-xs">
                                    <span class="w-3 text-gray-600">4</span>
                                    <div class="flex-1 mx-2">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: 30%"></div>
                                        </div>
                                    </div>
                                    <span class="w-8 text-right text-gray-700">374</span>
                                </div>
                                <div class="flex items-center text-xs">
                                    <span class="w-3 text-gray-600">3</span>
                                    <div class="flex-1 mx-2">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-yellow-500 h-1.5 rounded-full" style="width: 15%"></div>
                                        </div>
                                    </div>
                                    <span class="w-8 text-right text-gray-700">187</span>
                                </div>
                                <div class="flex items-center text-xs">
                                    <span class="w-3 text-gray-600">2</span>
                                    <div class="flex-1 mx-2">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-orange-500 h-1.5 rounded-full" style="width: 7%"></div>
                                        </div>
                                    </div>
                                    <span class="w-8 text-right text-gray-700">87</span>
                                </div>
                                <div class="flex items-center text-xs">
                                    <span class="w-3 text-gray-600">1</span>
                                    <div class="flex-1 mx-2">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-red-500 h-1.5 rounded-full" style="width: 3%"></div>
                                        </div>
                                    </div>
                                    <span class="w-8 text-right text-gray-700">37</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Section -->
                 <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Service Performance -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Service Performance</h3>
                            <button class="text-sm text-jru-blue hover:text-blue-800">View Details</button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600">Service Quality</span>
                                    <span class="font-medium">4.5/5.0</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 90%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600">Response Time</span>
                                    <span class="font-medium">4.2/5.0</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: 84%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600">Staff Courtesy</span>
                                    <span class="font-medium">4.7/5.0</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-jru-gold h-2 rounded-full" style="width: 94%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600">Process Efficiency</span>
                                    <span class="font-medium">3.8/5.0</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 76%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sentiment Analysis -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Sentiment Analysis</h3>
                            <button class="text-sm text-jru-blue hover:text-blue-800">View Details</button>
                        </div>
                        <div class="chart-container">
                            <canvas id="sentimentChart"></canvas>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-1">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-600">Positive</span>
                                </div>
                                <p class="text-lg font-bold text-gray-900">68%</p>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-1">
                                    <div class="w-3 h-3 bg-gray-400 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-600">Neutral</span>
                                </div>
                                <p class="text-lg font-bold text-gray-900">20%</p>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-1">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-600">Negative</span>
                                </div>
                                <p class="text-lg font-bold text-gray-900">12%</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bottom Section -->
                 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Satisfaction Trends -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Satisfaction Trends</h3>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-xs bg-jru-blue text-white rounded-full">7D</button>
                                <button class="px-3 py-1 text-xs text-gray-600 hover:bg-gray-100 rounded-full">30D</button>
                                <button class="px-3 py-1 text-xs text-gray-600 hover:bg-gray-100 rounded-full">90D</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="trendsChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Top Issues -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Common Feedback</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                <div class="flex items-center">
                    
                                    <span class="text-sm font-medium">Slow WiFi</span>
                                </div>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">23</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                <div class="flex items-center">
                                   
                                    <span class="text-sm font-medium">Limited Parking</span>
                                </div>
                                <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">18</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center">
                                   
                                    <span class="text-sm font-medium">Helpful Staff</span>
                                </div>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">45</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center">
                                   
                                    <span class="text-sm font-medium">Quick Service</span>
                                </div>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">32</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script> 
    
// Sidebar toggle functionality
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const logoContainer = document.getElementById('logoContainer');
const menuTexts = document.querySelectorAll('.menu-text'); // Includes all elements with menu-text
const quickActionsHeader = document.getElementById('quickActionsHeader'); // Specific header for quick actions

let sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true'; // Persist state

function applySidebarState() {
    if (sidebarCollapsed) {
        sidebar.classList.remove('sidebar-expanded');
        sidebar.classList.add('sidebar-collapsed');
       // logoContainer.classList.add('justify-center'); // Center logo when collapsed
       // logoContainer.querySelector('span').style.display = 'none'; // Hide text part of logo

        menuTexts.forEach(textEl => {
            // For direct children of nav or specific containers, hide them.
            // For icons or elements that should remain, they might not need specific styling here
            // if they are not tagged with menu-text or are handled by parent collapsing.
            if (!textEl.classList.contains('fas') && !textEl.closest('button#sidebarToggle')) { // Don't hide icons or the toggle button itself
                 // If the element is meant to be purely text that disappears.
                if (textEl.tagName === 'SPAN' || textEl.tagName === 'P' || textEl.classList.contains('text-xs')) {
                    textEl.style.opacity = '0';
                    textEl.style.display = 'none';
                }
            }
        });
         // Ensure user info text is hidden
        const userInfoText = document.getElementById('userInfo');
        if(userInfoText) {
            userInfoText.querySelectorAll('p').forEach(p => { p.style.display = 'none'; });
        }


    } else {
        sidebar.classList.remove('sidebar-collapsed');
        sidebar.classList.add('sidebar-expanded');
        //logoContainer.classList.remove('justify-center');
       // logoContainer.querySelector('span').style.display = 'inline'; // Show text part of logo

        menuTexts.forEach(textEl => {
            if (!textEl.classList.contains('fas') && !textEl.closest('button#sidebarToggle')) {
                 if (textEl.tagName === 'SPAN' || textEl.tagName === 'P' || textEl.classList.contains('text-xs')) {
                    textEl.style.display = 'block'; // Or 'inline', 'flex' depending on original
                    textEl.style.opacity = '1';
                }
            }
        });
        // Ensure user info text is shown
        const userInfoText = document.getElementById('userInfo');
        if(userInfoText) {
           userInfoText.querySelectorAll('p').forEach(p => { p.style.display = 'block'; });
        }
    }
}


sidebarToggle.addEventListener('click', function() {
    sidebarCollapsed = !sidebarCollapsed;
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
    applySidebarState();
});

// Apply initial state on load
document.addEventListener('DOMContentLoaded', () => {
    applySidebarState(); // Apply sidebar state first
    createCharts();    // Then create charts
});


// Charts initialization
function createCharts() {
    // Ensure canvas elements exist before creating charts
    const sentimentCanvas = document.getElementById('sentimentChart');
    const trendsCanvas = document.getElementById('trendsChart');

    if (sentimentCanvas) {
        new Chart(sentimentCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Neutral', 'Negative'],
                datasets: [{
                    data: [68, 20, 12], // Example data
                    backgroundColor: ['#10b981', '#9ca3af', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    }
    
    if (trendsCanvas) {
        new Chart(trendsCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], // Example labels
                datasets: [{
                    label: 'Satisfaction Score',
                    data: [4.1, 4.3, 4.0, 4.5, 4.2, 4.4, 4.2], // Example data
                    borderColor: tailwind.config.theme.extend.colors['jru-gold'],
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2, // Thinner line
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: tailwind.config.theme.extend.colors['jru-gold'],
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5, // Slightly smaller points
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 3.5,
                        max: 5,
                        grid: { color: '#e5e7eb' }, // Lighter grid lines
                        ticks: { color: '#6b7280' }  // Axis ticks color
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7280' }
                    }
                }
            }
        });
    }
}

// Handle window resize for charts (Chart.js v3+)
// Chart.js v3+ typically handles resize automatically if maintainAspectRatio is false.
// This explicit handling might be redundant or useful for older versions or specific cases.
// window.addEventListener('resize', function() {
//     Chart.instances.forEach(instance => { // This is for Chart.js v2. For v3+, it's different.
//         instance.resize();
//     });
// });
   
    </script>
</body>
</html>