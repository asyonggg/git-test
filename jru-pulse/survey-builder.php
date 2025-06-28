<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Builder - JRU-A-PULSE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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
        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
        
        .question-item {
            transition: all 0.3s ease;
        }
        
        .question-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .sortable-ghost {
            opacity: 0.4;
        }
        
        .sortable-chosen {
            transform: rotate(5deg);
        }
        
        .drag-handle {
            cursor: grab;
        }
        
        .drag-handle:active {
            cursor: grabbing;
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
                        <button id="quickNewSurvey" class="flex items-center w-full px-3 py-2 text-sm text-gray-50 hover:bg-gray-600 rounded-lg transition-colors">
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
                        <h1 class="text-2xl font-bold text-gray-900">Survey Builder</h1>
                        <p class="text-sm text-gray-600 mt-1">Create and customize surveys with templates</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="previewSurvey" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                            <i class="fas fa-eye mr-2"></i>
                            Preview
                        </button>
                        <button id="saveDraft" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Save Draft
                        </button>
                        <button id="publishSurvey" class="bg-jru-blue text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors flex items-center">
                            <i class="fas fa-rocket mr-2"></i>
                            Publish Survey
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Survey Builder Content -->
            <main class="flex-1 overflow-hidden">
                <div class="h-full flex">
                    <!-- Left Panel - Survey Builder -->
                    <div class="flex-1 overflow-y-auto p-6">
                        <!-- Survey Basic Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Survey Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Survey Title</label>
                                    <input type="text" id="surveyTitle" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" placeholder="Enter survey title" value="IT Services Experience Survey">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Office</label>
                                    <select id="surveyOffice" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                                        <option value="it" selected>Information Technology Office</option>
                                        <option value="registrar">Registrar's Office</option>
                                        <option value="library">Library</option>
                                        <option value="cashier">Cashier</option>
                                        <option value="medical">Medical & Dental Clinic</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                                    <select id="surveyService" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                                        <option value="classroom-assistance" selected>Classroom Technical Assistance</option>
                                        <option value="online-inquiry">Online Inquiry / Technical assistance</option>
                                        <option value="face-to-face">Face-To-Face inquiry assistance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Template</label>
                                    <select id="surveyTemplate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                                        <option value="standard" selected>Standard Service Template</option>
                                        <option value="it-specific">IT Services Template</option>
                                        <option value="custom">Custom Template</option>
                                        <option value="blank">Start from Blank</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Template Actions -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Template Actions</h3>
                                <div class="flex space-x-2">
                                    <button id="loadTemplate" class="bg-blue-100 text-blue-800 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-download mr-1"></i>
                                        Load Template
                                    </button>
                                    <button id="saveAsTemplate" class="bg-green-100 text-green-800 px-3 py-1 rounded-lg text-sm hover:bg-green-200 transition-colors">
                                        <i class="fas fa-save mr-1"></i>
                                        Save as Template
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600">Load a template to start with predefined questions, or save your current survey as a template for future use.</p>
                        </div>

                        <!-- Questions Builder -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Survey Questions</h3>
                                <div class="flex space-x-2">
                                    <button id="addQuestion" class="bg-jru-blue text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors flex items-center">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Question
                                    </button>
                                    <button id="addSection" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                                        <i class="fas fa-layer-group mr-2"></i>
                                        Add Section
                                    </button>
                                </div>
                            </div>

                            <!-- Questions List -->
                            <div id="questionsList" class="space-y-4">
                                <!-- Questions will be dynamically added here -->
                            </div>

                            <!-- Add Question Button (Bottom) -->
                            <div class="mt-6 text-center">
                                <button id="addQuestionBottom" class="w-full border-2 border-dashed border-gray-300 rounded-lg py-4 text-gray-500 hover:border-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add New Question
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel - Live Preview -->
                    <div class="w-96 bg-gray-100 border-l border-gray-200 overflow-y-auto">
                        <div class="p-4 bg-white border-b border-gray-200">
                            <h3 class="font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-mobile-alt mr-2 text-jru-blue"></i>
                                Live Preview
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">See how your survey will look to students</p>
                        </div>
                        
                        <div class="p-4">
                            <div id="surveyPreview" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 space-y-4">
                                <!-- Preview content will be generated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Question Editor Modal -->
    <div id="questionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Edit Question</h2>
                        <button id="closeQuestionModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <form id="questionForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Question Type</label>
                                <select id="questionType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                                    <option value="likert">Likert Scale (1-5)</option>
                                    <option value="text">Text Response</option>
                                    <option value="textarea">Long Text</option>
                                    <option value="multiple">Multiple Choice</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="rating">Star Rating</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Required</label>
                                <div class="flex items-center space-x-4 mt-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="required" value="true" class="mr-2" checked>
                                        <span class="text-sm">Required</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="required" value="false" class="mr-2">
                                        <span class="text-sm">Optional</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                            <textarea id="questionText" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" placeholder="Enter your question here..."></textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Help Text (Optional)</label>
                            <input type="text" id="questionHelp" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" placeholder="Additional instructions or context">
                        </div>

                        <!-- Options for Multiple Choice/Checkbox -->
                        <div id="optionsSection" class="mb-6 hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
                            <div id="optionsList" class="space-y-2">
                                <!-- Options will be added dynamically -->
                            </div>
                            <button type="button" id="addOption" class="mt-2 text-jru-blue hover:text-blue-800 text-sm">
                                <i class="fas fa-plus mr-1"></i>
                                Add Option
                            </button>
                        </div>

                        <!-- Likert Scale Customization -->
                        <div id="likertSection" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Scale Labels</label>
                            <div class="grid grid-cols-5 gap-2">
                                <input type="text" placeholder="1 - Poor" class="px-2 py-1 border border-gray-300 rounded text-sm">
                                <input type="text" placeholder="2 - Fair" class="px-2 py-1 border border-gray-300 rounded text-sm">
                                <input type="text" placeholder="3 - Good" class="px-2 py-1 border border-gray-300 rounded text-sm">
                                <input type="text" placeholder="4 - Very Good" class="px-2 py-1 border border-gray-300 rounded text-sm">
                                <input type="text" placeholder="5 - Excellent" class="px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Advanced Settings</h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" id="randomizeOptions" class="mr-2">
                                    <span class="text-sm text-gray-700">Randomize answer options</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="allowOther" class="mr-2">
                                    <span class="text-sm text-gray-700">Allow "Other" option</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="showInPreview" class="mr-2" checked>
                                    <span class="text-sm text-gray-700">Show in preview</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <button type="button" id="cancelQuestion" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-jru-blue text-white rounded-lg hover:bg-blue-800 transition-colors">
                                Save Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Modal -->
    <div id="templateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Save as Template</h2>
                        <button id="closeTemplateModal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <form id="templateForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                            <input type="text" id="templateName" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" placeholder="Enter template name">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea id="templateDescription" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue" placeholder="Describe when to use this template..."></textarea>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="templateCategory" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-jru-blue">
                                <option value="general">General Services</option>
                                <option value="it">IT Services</option>
                                <option value="academic">Academic Services</option>
                                <option value="administrative">Administrative Services</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        
                        <div class="flex justify-end space-x-4">
                            <button type="button" id="cancelTemplate" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-jru-blue text-white rounded-lg hover:bg-blue-800 transition-colors">
                                Save Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        <!-- Success and Error Modal -->
    <div id="toastNotification" class="fixed top-5 right-5 text-white py-3 px-6 rounded-lg shadow-xl z-[100] transition-all duration-300 ease-in-out opacity-0 hidden">
    <div class="flex items-center">
        <i id="toastIcon" class="mr-3 text-xl"></i>
        <span id="toastMessage" class="font-medium"></span>
    </div>
    </div>

    <script src="js/survey-builder.js">  </script>
</body>
</html>
