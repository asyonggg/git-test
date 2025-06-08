<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Management - JRU-A-PULSE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jru-blue': '#1e3a8a',
                        'jru-orange': '#f59e0b',
                        'jru-gold': '#fbbf24',
                    }
                }
            }
        }
    </script>
   
     <style>
        /* Sidebar */
        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .chart-container {
            position: relative;
            height: 220px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 180px;
            }
        }

        .sidebar-collapsed {
            width: 5rem;
        }

        .sidebar-expanded {
            width: 16rem;
        }

        .menu-text {
            transition: opacity 0.2s ease-in-out;
        }

        .logo-transition {
            transition: all 0.3s ease;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
       <div id="sidebar" class="sidebar-transition sidebar-expanded bg-blue-950 shadow-lg flex flex-col border-r border-gray-200">
            <!-- Logo Section -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="p-2 rounded-lg hover:bg-gray-600 transition-colors mr-3">
                        <i class="fas fa-bars text-gray-100"></i>
                    </button>
                    <div id="logoContainer" class="logo-transition flex items-center">
                        <img src="assets\jru-pulse-final-white.png" alt="JRU-A-PULSE" class="h-8 w-auto">
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors">
                            <i class="fas fa-tachometer-alt text-lg w-6"></i>
                            <span class="menu-text ml-3">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="survey-management.php" class="flex items-center px-3 py-3 bg-blue-50 text-jru-blue rounded-lg font-medium">
                            <i class="fas fa-poll text-lg w-6"></i>
                            <span class="menu-text ml-3">Survey Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="performance-analytics-reports.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors">
                            <i class="fas fa-chart-line text-lg w-6"></i>
                            <span class="menu-text ml-3">Performance Analytics & Reports</span>
                        </a>
                    </li>
                   
                    <li>
                        <a href="#" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors">
                            <i class="fas fa-users text-lg w-6"></i>
                            <span class="menu-text ml-3">User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors">
                            <i class="fas fa-cog text-lg w-6"></i>
                            <span class="menu-text ml-3">Settings</span>
                        </a>
                    </li>
                </ul>
                <br>
                <!-- Quick Actions -->
                <div class="mt-8">
                    <div id="quickActionsHeader" class="menu-text text-xs font-semibold text-gray-50 uppercase tracking-wider mb-3">
                        Quick Actions
                    </div>
                    <div class="space-y-2">
                        <button class="flex items-center w-full px-3 py-2 text-sm text-gray-50 hover:bg-gray-600 rounded-lg transition-colors">
                            <i class="fas fa-plus text-sm w-6"></i>
                            <span class="menu-text ml-3">New Survey</span>
                        </button>
                        <button class="flex items-center w-full px-3 py-2 text-sm text-gray-50 hover:bg-gray-600 rounded-lg transition-colors">
                            <i class="fas fa-download text-sm w-6"></i>
                            <span class="menu-text ml-3">Export Data</span>
                        </button>
                    </div>
                </div>
            </nav>
            
            <!-- User Profile -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-jru-gold to-yellow-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <div id="userInfo" class="menu-text ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-50">Administrator</p>
                        <p class="text-xs text-gray-100">gto@jru.edu.ph</p>
                    </div>
                    <button class="menu-text p-2 text-gray-50 hover:text-yellow-400 transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Survey Management</h1>
                        <p class="text-sm text-gray-600 mt-1">Manage offices, services, and create feedback surveys</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search surveys..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        
                        <!-- Create Survey Button -->
                        <button id="createSurveyBtn" class="bg-jru-blue text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Create Survey
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Management Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button id="surveysTab" class="tab-button active border-b-2 border-jru-blue text-jru-blue py-2 px-1 text-sm font-medium">
                                Surveys
                            </button>
                            <button id="officesTab" class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-2 px-1 text-sm font-medium">
                                Offices & Services
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Surveys Tab Content -->
                <div id="surveysContent" class="tab-content">
                    <!-- Survey Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100">
                                    <i class="fas fa-poll text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Surveys</p>
                                    <p id="totalSurveys" class="text-2xl font-bold text-gray-900">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100">
                                    <i class="fas fa-play text-green-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Active Surveys</p>
                                    <p id="activeSurveys" class="text-2xl font-bold text-gray-900">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-100">
                                    <i class="fas fa-edit text-yellow-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Draft Surveys</p>
                                    <p id="draftSurveys" class="text-2xl font-bold text-gray-900">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100">
                                    <i class="fas fa-chart-bar text-purple-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Responses</p>
                                    <p id="totalResponses" class="text-2xl font-bold text-gray-900">0</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Surveys List -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Recent Surveys</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Survey</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Office</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responses</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="surveysTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Surveys will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Offices & Services Tab Content -->
                <div id="officesContent" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Offices Management -->
                        <div class="bg-white rounded-lg shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Offices</h3>
                                <button id="addOfficeBtn" class="bg-jru-blue text-white px-3 py-1 rounded text-sm hover:bg-blue-800">
                                    <i class="fas fa-plus mr-1"></i>Add Office
                                </button>
                            </div>
                            <div class="p-6">
                                <div id="officesList" class="space-y-3">
                                    <!-- Offices will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <!-- Services Management -->
                        <div class="bg-white rounded-lg shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Services</h3>
                                <button id="addServiceBtn" class="bg-jru-orange text-white px-3 py-1 rounded text-sm hover:bg-orange-600">
                                    <i class="fas fa-plus mr-1"></i>Add Service
                                </button>
                            </div>
                            <div class="p-6">
                                <div class="mb-4">
                                    <select id="officeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                        <option value="">Select office to view services</option>
                                    </select>
                                </div>
                                <div id="servicesList" class="space-y-3">
                                    <!-- Services will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Survey Modal -->
    <div id="createSurveyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Create New Survey</h2>
                        <button id="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <form id="createSurveyForm">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Survey Title</label>
                                <input type="text" id="newSurveyTitle" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" placeholder="Enter survey title" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="newSurveyDescription" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" placeholder="Enter survey description"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Office</label>
                                    <select id="newSurveyOffice" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" required>
                                        <option value="">Select Office</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                                    <select id="newSurveyService" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" required disabled>
                                        <option value="">Select Service</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-4 mt-6">
                            <button type="button" id="cancelCreate" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-jru-blue text-white rounded-lg hover:bg-blue-800 transition-colors">
                                Create & Build Survey
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Office Modal -->
    <div id="addOfficeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Add New Office</h2>
                </div>
                <div class="p-6">
                    <form id="addOfficeForm">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Office Name</label>
                                <input type="text" id="newOfficeName" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Office Code</label>
                                <input type="text" id="newOfficeCode" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="newOfficeDescription" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-4 mt-6">
                            <button type="button" id="cancelAddOffice" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-jru-blue text-white rounded-lg">Add Office</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Add New Service</h2>
                </div>
                <div class="p-6">
                    <form id="addServiceForm">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Office</label>
                                <select id="newServiceOffice" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                    <option value="">Select Office</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Service Name</label>
                                <input type="text" id="newServiceName" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Service Code</label>
                                <input type="text" id="newServiceCode" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="newServiceDescription" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-4 mt-6">
                            <button type="button" id="cancelAddService" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-jru-orange text-white rounded-lg">Add Service</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
                // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoContainer = document.getElementById('logoContainer');
        const menuTexts = document.querySelectorAll('.menu-text');
        const userInfo = document.getElementById('userInfo');
        const quickActionsHeader = document.getElementById('quickActionsHeader');

        let sidebarCollapsed = false;

        sidebarToggle.addEventListener('click', function() {
            sidebarCollapsed = !sidebarCollapsed;
            
            if (sidebarCollapsed) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                
                // Hide text elements
                menuTexts.forEach(text => {
                    text.style.opacity = '0';
                    setTimeout(() => {
                        text.style.display = 'none';
                    }, 150);
                });
                
                // Hide logo
                logoContainer.style.opacity = '0';
                setTimeout(() => {
                    logoContainer.style.display = 'none';
                }, 150);
                
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                
                // Show text elements
                setTimeout(() => {
                    menuTexts.forEach(text => {
                        text.style.display = 'block';
                        setTimeout(() => {
                            text.style.opacity = '1';
                        }, 50);
                    });
                    
                    // Show logo
                    logoContainer.style.display = 'flex';
                    setTimeout(() => {
                        logoContainer.style.opacity = '1';
                    }, 50);
                }, 150);
            }
        });
        

        // Global state
        let offices = [];
        let services = [];
        let surveys = [];
        let currentTab = 'surveys';

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
            setupEventListeners();
        });

        async function initializeApp() {
            await loadOffices();
            await loadServices();
            await loadSurveys();
            updateStatistics();
            populateOfficeSelects();
        }

        function setupEventListeners() {
            // Tab switching
            document.getElementById('surveysTab').addEventListener('click', () => switchTab('surveys'));
            document.getElementById('officesTab').addEventListener('click', () => switchTab('offices'));

            // Survey creation
            const createSurveyBtn = document.getElementById('createSurveyBtn');
            if (createSurveyBtn) {
                createSurveyBtn.addEventListener('click', function(e) {
                    console.log('Create Survey button clicked');
                    e.preventDefault();
                    openCreateSurveyModal();
                });
            }

            // Survey creation - Quick action button
            const quickNewSurvey = document.getElementById('quickNewSurvey');
            if (quickNewSurvey) {
                quickNewSurvey.addEventListener('click', function(e) {
                    console.log('Quick New Survey button clicked');
                    e.preventDefault();
                    openCreateSurveyModal();
                });
            }
            
            // Modal close buttons
            const closeCreateModal = document.getElementById('closeCreateModal');
            if (closeCreateModal) {
                closeCreateModal.addEventListener('click', function(e) {
                    console.log('Close modal X button clicked');
                    e.preventDefault();
                    closeCreateSurveyModal();
                });
            }
            
            const cancelCreate = document.getElementById('cancelCreate');
            if (cancelCreate) {
                cancelCreate.addEventListener('click', function(e) {
                    console.log('Cancel button clicked');
                    e.preventDefault();
                    closeCreateSurveyModal();
                });
            }
            document.getElementById('createSurveyForm').addEventListener('submit', handleCreateSurvey);

            // Office/Service management
            document.getElementById('addOfficeBtn').addEventListener('click', openAddOfficeModal);
            document.getElementById('addServiceBtn').addEventListener('click', openAddServiceModal);
            document.getElementById('cancelAddOffice').addEventListener('click', closeAddOfficeModal);
            document.getElementById('cancelAddService').addEventListener('click', closeAddServiceModal);
            document.getElementById('addOfficeForm').addEventListener('submit', handleAddOffice);
            document.getElementById('addServiceForm').addEventListener('submit', handleAddService);

            // Office selection for services
            document.getElementById('newSurveyOffice').addEventListener('change', handleOfficeChange);
            document.getElementById('officeFilter').addEventListener('change', filterServices);

            // Sidebar toggle
            document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);

            // Search
            document.getElementById('searchInput').addEventListener('input', handleSearch);
        }

        // Tab Management
        function switchTab(tab) {
            currentTab = tab;
            
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active', 'border-jru-blue', 'text-jru-blue');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            document.getElementById(tab + 'Tab').classList.add('active', 'border-jru-blue', 'text-jru-blue');
            document.getElementById(tab + 'Tab').classList.remove('border-transparent', 'text-gray-500');
            
            // Show/hide content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            document.getElementById(tab + 'Content').classList.remove('hidden');
            
            if (tab === 'offices') {
                renderOffices();
                renderServices();
            }
        }

        // Data Loading Functions
        async function loadOffices() {
            try {
                const response = await fetch('/api/offices.php');
                const result = await response.json();
                if (result.success) {
                    offices = result.data;
                }
            } catch (error) {
                console.error('Error loading offices:', error);
                // Use fallback data for demo
                offices = [
                    {id: 1, name: "Registrar's Office", code: "REG"},
                    {id: 2, name: "Student Accounts Office", code: "SAO"},
                    {id: 3, name: "Cashier", code: "CASH"},
                    {id: 4, name: "Library", code: "LIB"},
                    {id: 5, name: "Information Technology Office", code: "IT"},
                    {id: 6, name: "Medical & Dental Clinic", code: "MED"},
                    {id: 7, name: "Guidance & Testing Office", code: "GTO"},
                    {id: 8, name: "Student Development Office", code: "SDO"},
                    {id: 9, name: "Athletics Office", code: "ATH"},
                    {id: 10, name: "Customer Advocacy Office", code: "CAO"},
                    {id: 11, name: "Engineering and Maintenance Office", code: "EMO"}
                ];
            }
        }

        async function loadServices() {
            try {
                const response = await fetch('/api/services.php');
                const result = await response.json();
                if (result.success) {
                    services = result.data;
                }
            } catch (error) {
                console.error('Error loading services:', error);
                // Use fallback data for demo
                services = [
                    {id: 1, office_id: 1, name: "Document request", code: "document-request"},
                    {id: 2, office_id: 2, name: "Onsite inquiry", code: "onsite-inquiry"},
                    {id: 3, office_id: 2, name: "Online inquiry", code: "online-inquiry"},
                    {id: 4, office_id: 3, name: "Onsite Payment", code: "onsite-payment"},
                    {id: 5, office_id: 4, name: "Online Library Services (Email, social media platforms)", code: "online-library-services"},
                    {id: 6, office_id: 4, name: "Face-to-Face Library Services", code: "face-to-face-library"},
                    {id: 7, office_id: 4, name: "Borrowing of printed materials", code: "borrowing-materials"},
                    {id: 8, office_id: 4, name: "Online Library Instructions", code: "online-instructions"},
                    {id: 9, office_id: 4, name: "Participation on Library activities and programs", code: "library-activities"},
                    {id: 10, office_id: 5, name: "Online Inquiry / Technical assistance", code: "online-tech-assistance"},
                    {id: 11, office_id: 5, name: "Face-To-Face inquiry assistance", code: "face-to-face-tech"},
                    {id: 12, office_id: 5, name: "Technical Assistance during events", code: "event-tech-support"},
                    {id: 13, office_id: 5, name: "Classroom/Office Technical Assistance", code: "classroom-tech-support"}
                ];
            }
        }

        async function loadSurveys() {
            try {
                const response = await fetch('/api/surveys.php');
                const result = await response.json();
                if (result.success) {
                    surveys = result.data;
                    renderSurveys();
                }
            } catch (error) {
                console.error('Error loading surveys:', error);
                surveys = []; // Empty for demo
                renderSurveys();
            }
        }

        // Rendering Functions
        function renderSurveys() {
            const tbody = document.getElementById('surveysTableBody');
            
            if (surveys.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-poll text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">No surveys found</p>
                                <p class="text-sm">Create your first survey to get started</p>
                                <button onclick="openCreateSurveyModal()" class="mt-4 bg-jru-blue text-white px-4 py-2 rounded-lg">
                                    Create Survey
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = surveys.map(survey => {
                const office = offices.find(o => o.id == survey.office_id);
                const service = services.find(s => s.id == survey.service_id);
                
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">${survey.title}</div>
                                <div class="text-sm text-gray-500">${survey.description || 'No description'}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">${office ? office.name : 'Unknown'}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">${service ? service.name : 'Unknown'}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(survey.status)}">
                                ${survey.status}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">${survey.response_count || 0}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">${formatDate(survey.created_at)}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="editSurvey(${survey.id})" class="text-jru-blue hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="viewSurvey(${survey.id})" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="deleteSurvey(${survey.id})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderOffices() {
            const container = document.getElementById('officesList');
            container.innerHTML = offices.map(office => `
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div>
                        <div class="font-medium text-gray-900">${office.name}</div>
                        <div class="text-sm text-gray-500">Code: ${office.code}</div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="editOffice(${office.id})" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteOffice(${office.id})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function renderServices() {
            const selectedOfficeId = document.getElementById('officeFilter').value;
            const filteredServices = selectedOfficeId ? 
                services.filter(s => s.office_id == selectedOfficeId) : 
                services;

            const container = document.getElementById('servicesList');
            
            if (filteredServices.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No services found</p>';
                return;
            }

            container.innerHTML = filteredServices.map(service => {
                const office = offices.find(o => o.id == service.office_id);
                return `
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900">${service.name}</div>
                            <div class="text-sm text-gray-500">${office ? office.name : 'Unknown Office'}</div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="editService(${service.id})" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteService(${service.id})" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Modal Management
        function openCreateSurveyModal() {
            document.getElementById('createSurveyModal').classList.remove('hidden');
        }

        function closeCreateSurveyModal() {
            document.getElementById('createSurveyModal').classList.add('hidden');
            document.getElementById('createSurveyForm').reset();
        }

        function openAddOfficeModal() {
            document.getElementById('addOfficeModal').classList.remove('hidden');
        }

        function closeAddOfficeModal() {
            document.getElementById('addOfficeModal').classList.add('hidden');
            document.getElementById('addOfficeForm').reset();
        }

        function openAddServiceModal() {
            document.getElementById('addServiceModal').classList.remove('hidden');
        }

        function closeAddServiceModal() {
            document.getElementById('addServiceModal').classList.add('hidden');
            document.getElementById('addServiceForm').reset();
        }

        // Form Handlers
        async function handleCreateSurvey(e) {
            e.preventDefault();
            
            const formData = {
                title: document.getElementById('newSurveyTitle').value,
                description: document.getElementById('newSurveyDescription').value,
                office_id: document.getElementById('newSurveyOffice').value,
                service_id: document.getElementById('newSurveyService').value
            };

            // Redirect to survey builder with parameters
            const params = new URLSearchParams(formData);
            window.location.href = `survey-builder.php?${params.toString()}`;
        }

        async function handleAddOffice(e) {
            e.preventDefault();
            
            const newOffice = {
                name: document.getElementById('newOfficeName').value,
                code: document.getElementById('newOfficeCode').value,
                description: document.getElementById('newOfficeDescription').value
            };

            // Add to local array for demo
            offices.push({
                id: offices.length + 1,
                ...newOffice
            });

            renderOffices();
            populateOfficeSelects();
            closeAddOfficeModal();
            alert('Office added successfully!');
        }

        async function handleAddService(e) {
            e.preventDefault();
            
            const newService = {
                office_id: document.getElementById('newServiceOffice').value,
                name: document.getElementById('newServiceName').value,
                code: document.getElementById('newServiceCode').value,
                description: document.getElementById('newServiceDescription').value
            };

            // Add to local array for demo
            services.push({
                id: services.length + 1,
                ...newService
            });

            renderServices();
            closeAddServiceModal();
            alert('Service added successfully!');
        }

        // Helper Functions
        function populateOfficeSelects() {
            const selects = [
                document.getElementById('newSurveyOffice'),
                document.getElementById('officeFilter'),
                document.getElementById('newServiceOffice')
            ];

            selects.forEach(select => {
                if (!select) return;
                
                const currentValue = select.value;
                const isFilter = select.id === 'officeFilter';
                
                select.innerHTML = isFilter ? 
                    '<option value="">All Offices</option>' : 
                    '<option value="">Select Office</option>';
                
                offices.forEach(office => {
                    const option = document.createElement('option');
                    option.value = office.id;
                    option.textContent = office.name;
                    select.appendChild(option);
                });
                
                if (currentValue) select.value = currentValue;
            });
        }

        function handleOfficeChange() {
            const officeId = document.getElementById('newSurveyOffice').value;
            const serviceSelect = document.getElementById('newSurveyService');
            
            serviceSelect.innerHTML = '<option value="">Select Service</option>';
            serviceSelect.disabled = !officeId;
            
            if (officeId) {
                const officeServices = services.filter(s => s.office_id == officeId);
                officeServices.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.name;
                    serviceSelect.appendChild(option);
                });
            }
        }

        function filterServices() {
            renderServices();
        }

        function updateStatistics() {
            document.getElementById('totalSurveys').textContent = surveys.length;
            document.getElementById('activeSurveys').textContent = surveys.filter(s => s.status === 'active').length;
            document.getElementById('draftSurveys').textContent = surveys.filter(s => s.status === 'draft').length;
            document.getElementById('totalResponses').textContent = surveys.reduce((sum, s) => sum + (s.response_count || 0), 0);
        }

        function getStatusClass(status) {
            const classes = {
                'draft': 'bg-yellow-100 text-yellow-800',
                'active': 'bg-green-100 text-green-800',
                'paused': 'bg-gray-100 text-gray-800',
                'completed': 'bg-blue-100 text-blue-800',
                'archived': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }

        function editSurvey(id) {
            window.location.href = `survey-builder.php?id=${id}`;
        }

        function viewSurvey(id) {
            window.open(`survey-preview.php?id=${id}`, '_blank');
        }

        function deleteSurvey(id) {
            if (confirm('Are you sure you want to delete this survey?')) {
                surveys = surveys.filter(s => s.id !== id);
                renderSurveys();
                updateStatistics();
                alert('Survey deleted successfully!');
            }
        }

        function handleSearch() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            // Implement search functionality
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isExpanded = sidebar.classList.contains('sidebar-expanded');
            
            if (isExpanded) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
            }
        }
    </script>
</body>
</html>