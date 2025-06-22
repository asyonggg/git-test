<?php
session_start(); // Must be at the top

// Check if the user is logged in and is an admin, otherwise redirect
if (!isset($_SESSION['user_data']) || $_SESSION['user_data']['role'] !== 'admin') {
    header('Location: index.html?error=auth_required');
    exit;
}

// Get user data from session for the header/sidebar
$user = $_SESSION['user_data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - JRU-PULSE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .sidebar-transition { transition: all .3s cubic-bezier(.4, 0, .2, 1); }
        .sidebar-collapsed { width: 5rem; }
        .sidebar-expanded { width: 16rem; }
        .menu-text { transition: opacity .2s ease-in-out; }
        .logo-transition { transition: all .3s ease; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }

        .modal-overlay { transition: opacity 0.3s ease; }
        #toast-notification {
            transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-transition sidebar-expanded bg-blue-950 shadow-lg flex flex-col border-r border-gray-200">
            <div class="p-4 border-b border-gray-200"><div class="flex items-center"><button id="sidebarToggle" class="p-2 rounded-lg hover:bg-gray-600 transition-colors mr-3"><i class="fas fa-bars text-gray-100"></i></button><div id="logoContainer" class="logo-transition flex items-center"><img src="assets/jru-pulse-final-white.png" alt="JRU-A-PULSE" class="h-8 w-auto"></div></div></div>
            <nav class="flex-1 p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <li><a href="dashboard.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-tachometer-alt text-lg w-6"></i><span class="menu-text ml-3">Dashboard</span></a></li>
                    <li><a href="survey-management.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-poll text-lg w-6"></i><span class="menu-text ml-3">Survey Management</span></a></li>
                    <li><a href="performance-analytics-reports.php" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-chart-line text-lg w-6"></i><span class="menu-text ml-3">Performance Analytics & Reports</span></a></li>
                    <li><a href="user_management.php" class="flex items-center px-3 py-3 bg-blue-50 text-jru-blue rounded-lg font-medium"><i class="fas fa-users text-lg w-6"></i><span class="menu-text ml-3">User Management</span></a></li>
                    <li><a href="#" class="flex items-center px-3 py-3 text-gray-50 hover:bg-gray-600 rounded-lg transition-colors"><i class="fas fa-cog text-lg w-6"></i><span class="menu-text ml-3">Settings</span></a></li>
                </ul>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <?php if (isset($user['picture']) && !empty($user['picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="User Picture" class="w-10 h-10 rounded-full object-cover mr-3">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-gradient-to-r from-jru-gold to-yellow-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-3"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
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
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                        <p class="text-sm text-gray-600 mt-1">Add, edit, or remove user roles and permissions.</p>
                    </div>
                </div>
            </header>
            
            <!-- User Management Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Authorized Users</h2>
                        <button id="add-user-btn" class="bg-jru-blue text-white font-semibold px-4 py-2 rounded-lg hover:bg-jru-navy transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add New User
                        </button>
                    </div>
                    
                    <!-- Users Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Office</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body" class="bg-white divide-y divide-gray-200">
                                <!-- User rows will be populated here by JavaScript -->
                                <tr><td colspan="4" class="text-center p-8 text-gray-500">Loading users...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="user-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <form id="user-form">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 id="modal-title" class="text-xl font-semibold text-gray-900">Add New User</h3>
                    <button type="button" class="close-modal-btn text-gray-400 hover:text-gray-600"><i class="fas fa-times text-2xl"></i></button>
                </div>
                <div class="p-6 space-y-4">
                    <input type="hidden" id="user-id">
                    <div>
                        <label for="user-email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="user-email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-jru-blue focus:border-jru-blue sm:text-sm">
                    </div>
                    <div>
                        <label for="user-role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="user-role" required class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-jru-blue focus:border-jru-blue sm:text-sm">
                            <option value="admin">Admin</option>
                            <option value="office_head">Office Head</option>
                        </select>
                    </div>
                    <div id="office-name-group" class="hidden">
                        <label for="office-name" class="block text-sm font-medium text-gray-700">Office Name</label>
                        <input type="text" id="office-name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-jru-blue focus:border-jru-blue sm:text-sm">
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button type="button" class="close-modal-btn bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jru-blue">Cancel</button>
                    <button type="submit" id="save-user-btn" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-jru-blue hover:bg-jru-navy focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-jru-blue">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
     <div id="delete-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="p-6 text-center">
                 <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                 </div>
                 <h3 class="mt-5 text-lg font-medium text-gray-900">Delete User</h3>
                 <div class="mt-2">
                    <p class="text-sm text-gray-500">Are you sure you want to delete this user? This action cannot be undone.</p>
                 </div>
                 <div class="mt-6 flex justify-center space-x-4">
                    <button type="button" id="cancel-delete-btn" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="button" id="confirm-delete-btn" class="bg-red-600 text-white py-2 px-4 rounded-md shadow-sm text-sm font-medium hover:bg-red-700">Delete</button>
                 </div>
            </div>
        </div>
     </div>

     <!-- Toast Notification -->
     <div id="toast-notification" class="fixed bottom-5 right-5 bg-jru-navy text-white py-3 px-5 rounded-lg shadow-lg transform translate-y-20 opacity-0">
        <p id="toast-message"></p>
     </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- REFERENCES ---
        const tableBody = document.getElementById('users-table-body');
        const userModal = document.getElementById('user-modal');
        const deleteModal = document.getElementById('delete-modal');
        const addUserBtn = document.getElementById('add-user-btn');
        const userForm = document.getElementById('user-form');
        const officeNameGroup = document.getElementById('office-name-group');
        let userIdToDelete = null;

        // --- CORRECT API PATH ---
        const API_URL = 'api/user_roles_api.php';

        // --- FUNCTIONS ---
        const fetchUsers = async () => {
            try {
                const response = await fetch(API_URL); // <-- CORRECTED PATH
                const data = await response.json();
                if (!data.success) throw new Error(data.error);
                
                tableBody.innerHTML = ''; // Clear loading state
                if (data.users.length === 0) {
                     tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-8 text-gray-500">No users found.</td></tr>`;
                     return;
                }

                data.users.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${user.email}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${user.role === 'admin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}">
                                ${user.role.replace('_', ' ')}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${user.office_name || 'N/A'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="edit-btn text-jru-blue hover:text-jru-navy" data-user='${JSON.stringify(user)}'><i class="fas fa-pencil-alt mr-2"></i>Edit</button>
                            <button class="delete-btn text-red-600 hover:text-red-800 ml-4" data-id="${user.id}"><i class="fas fa-trash-alt mr-2"></i>Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } catch (error) {
                console.error('Failed to fetch users:', error);
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-8 text-red-500">Could not load users.</td></tr>`;
            }
        };

        const showToast = (message, isError = false) => {
            const toast = document.getElementById('toast-notification');
            const toastMessage = document.getElementById('toast-message');
            toastMessage.textContent = message;
            toast.className = `fixed bottom-5 right-5 text-white py-3 px-5 rounded-lg shadow-lg transform translate-y-20 opacity-0 ${isError ? 'bg-red-600' : 'bg-jru-navy'}`;
            
            setTimeout(() => {
                toast.classList.remove('translate-y-20', 'opacity-0');
            }, 100);

            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 4000);
        };

        const openModal = (modal) => modal.classList.remove('hidden');
        const closeModal = (modal) => modal.classList.add('hidden');

        // --- EVENT LISTENERS ---
        addUserBtn.addEventListener('click', () => {
            userForm.reset();
            document.getElementById('modal-title').textContent = 'Add New User';
            document.getElementById('user-id').value = '';
            officeNameGroup.classList.add('hidden');
            openModal(userModal);
        });
        
        tableBody.addEventListener('click', e => {
            if (e.target.closest('.edit-btn')) {
                const button = e.target.closest('.edit-btn');
                const user = JSON.parse(button.dataset.user);
                userForm.reset();
                document.getElementById('modal-title').textContent = 'Edit User';
                document.getElementById('user-id').value = user.id;
                document.getElementById('user-email').value = user.email;
                document.getElementById('user-role').value = user.role;
                document.getElementById('office-name').value = user.office_name || '';
                
                if (user.role === 'office_head') {
                    officeNameGroup.classList.remove('hidden');
                } else {
                    officeNameGroup.classList.add('hidden');
                }
                openModal(userModal);
            }
            if (e.target.closest('.delete-btn')) {
                userIdToDelete = e.target.closest('.delete-btn').dataset.id;
                openModal(deleteModal);
            }
        });

        userForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('user-id').value;
            const isEditing = !!id;

            const userData = {
                email: document.getElementById('user-email').value,
                role: document.getElementById('user-role').value,
                office_name: document.getElementById('user-role').value === 'office_head' ? document.getElementById('office-name').value : null
            };

            const method = isEditing ? 'PUT' : 'POST';
            if (isEditing) userData.id = id;

            try {
                const response = await fetch(API_URL, { // <-- CORRECTED PATH
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(userData)
                });
                const result = await response.json();
                if (!result.success) throw new Error(result.error);
                
                showToast(`User ${isEditing ? 'updated' : 'added'} successfully.`);
                closeModal(userModal);
                fetchUsers();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        document.getElementById('user-role').addEventListener('change', (e) => {
            if (e.target.value === 'office_head') {
                officeNameGroup.classList.remove('hidden');
            } else {
                officeNameGroup.classList.add('hidden');
            }
        });

        document.querySelectorAll('.close-modal-btn').forEach(btn => btn.addEventListener('click', () => closeModal(userModal)));
        document.getElementById('cancel-delete-btn').addEventListener('click', () => closeModal(deleteModal));
        
        document.getElementById('confirm-delete-btn').addEventListener('click', async () => {
             try {
                const response = await fetch(API_URL, { // <-- CORRECTED PATH
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userIdToDelete })
                });
                const result = await response.json();
                if (!result.success) throw new Error(result.error);

                showToast('User deleted successfully.');
                closeModal(deleteModal);
                fetchUsers();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        // --- INITIAL LOAD ---
        fetchUsers();
    });
    </script>
</body>
</html>
