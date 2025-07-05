    const sidebar = document.getElementById('sidebar');     // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const logoContainer = document.getElementById('logoContainer');
    const menuTexts = document.querySelectorAll('.menu-text');
    const userInfo = document.getElementById('userInfo');
    const quickActionsHeader = document.getElementById('quickActionsHeader');

    let sidebarCollapsed = false;

    function toggleSidebar(){
        sidebarCollapsed = !sidebarCollapsed;

        if (sidebarCollapsed){
            sidebar.classList.remove('sidebar-expanded');
            sidebar.classList.add('sidebar-collapsed');

            menuTexts.forEach(text => {  //Itago ang mga tekstual na elemento
                text.style.opacity = '0';
                setTimeout(() => {
                    text.style.display = 'none';
                }, 150);
            });

            logoContainer.style.opacity ='0'; 
            setTimeout(() => {
            logoContainer.style.display = 'none';
            }, 150);

        } else {
            sidebar.classList.remove('sidebar-collapsed'); //Ipakita ang mga tekstual na elemento
            sidebar.classList.add('sidebar-expanded');
            
            setTimeout(() => {
                menuTexts.forEach(text => {
                    text.style.display = 'block';
                    setTimeout(() => {
                        text.style.opacity = '1';
                    }, 50);
                });

            logoContainer.style.display = 'flex';  //Ipakita ang logo
            setTimeout(() => {
                logoContainer.style.opacity = '1';
            }, 50);
        }, 150);
        }
    }
        
    sidebarToggle.addEventListener('click', toggleSidebar);  // Add the existing sidebar toggle event listener
        
        let offices = []; //office global array
        let services = []; //services global array
        let isShowingArchivedOffices = false;
        let isShowingArchivedServices = false;
        let surveys = [];
        let isShowingArchivedSurveys = false;
        let currentTab = 'surveys';

        
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
        
        document.getElementById('surveysTab').addEventListener('click', () => switchTab('surveys')); // Tab switching
        document.getElementById('officesTab').addEventListener('click', () => switchTab('offices'));

        const createSurveyBtn = document.getElementById('createSurveyBtn'); // Survey creation
        if (createSurveyBtn) {
            createSurveyBtn.addEventListener('click', function(e) {
                console.log('Create Survey button clicked');
                e.preventDefault();
                openCreateSurveyModal();
            });
        }

        const quickNewSurvey = document.getElementById('quickNewSurvey');  // Survey creation - Quick action button
        if (quickNewSurvey) {
            quickNewSurvey.addEventListener('click', function(e) {
                console.log('Quick New Survey button clicked');
                e.preventDefault();
                openCreateSurveyModal();
            });
        }
        
        const closeCreateModal = document.getElementById('closeCreateModal');     // Modal close buttons
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


        const createSurveyForm = document.getElementById('createSurveyForm');
        if (createSurveyForm) {
            createSurveyForm.addEventListener('submit', handleCreateSurvey);
        }

        // Add Office Modal
        const addOfficeBtn = document.getElementById('addOfficeBtn');
        if (addOfficeBtn) {
            addOfficeBtn.addEventListener('click', openAddOfficeModal);
        }
        
        //Add Service modal
        const addServiceBtn = document.getElementById('addServiceBtn');
        if (addServiceBtn) {
            addServiceBtn.addEventListener('click', openAddServiceModal);
        }
        
        // Modal close buttons
        const cancelAddOffice = document.getElementById('cancelAddOffice');
        if (cancelAddOffice) {
            cancelAddOffice.addEventListener('click', closeAddOfficeModal);
        }

            const cancelEditOfficeModal = document.getElementById('cancelEditOfficeModal');
        if (cancelEditOfficeModal) {
            cancelEditOfficeModal.addEventListener('click', closeEditOfficeModal);
        }


        const editOfficeForm = document.getElementById('editOfficeForm');
        if (editOfficeForm) {
            editOfficeForm.addEventListener('submit', handleUpdateOffice);
        }
        
        const cancelAddService = document.getElementById('cancelAddService');
        if (cancelAddService) {
            cancelAddService.addEventListener('click', closeAddServiceModal);
        }
        
        const addOfficeForm = document.getElementById('addOfficeForm');
        if (addOfficeForm) {
            addOfficeForm.addEventListener('submit', handleAddOffice);
        }
        
        const addServiceForm = document.getElementById('addServiceForm');
        if (addServiceForm) {
            addServiceForm.addEventListener('submit', handleAddService);
        }

        const newSurveyOffice = document.getElementById('newSurveyOffice'); // Office selection for services
        if (newSurveyOffice) {
            newSurveyOffice.addEventListener('change', handleOfficeChange);
        }
        
        const officeFilter = document.getElementById('officeFilter');
        if (officeFilter) {
            officeFilter.addEventListener('change', filterServices);
        }

        const searchInput = document.getElementById('searchInput'); // Search
        if (searchInput) {
            searchInput.addEventListener('input', handleSearch);
        }

        const showArchivedToggleOffices = document.getElementById('showArchivedToggleOffices'); //Show archived offices toggle
        if (showArchivedToggleOffices) {
            showArchivedToggleOffices.addEventListener('change', handleShowArchivedToggleOffices);
        }

        const showArchivedToggleServices = document.getElementById('showArchivedToggleServices'); //Show archived services toggle - 1st
        if (showArchivedToggleServices){
            showArchivedToggleServices.addEventListener('change', handleShowArchivedServices);
        }

        const cancelEditServiceModal = document.getElementById('cancelEditServiceModal');
        if (cancelEditServiceModal) {
            cancelEditServiceModal.addEventListener('click', closeEditServiceModal);
        }
        
        const editServiceForm = document.getElementById('editServiceForm');
        if (editServiceForm) {
            editServiceForm.addEventListener('submit', handleUpdateService);
        }

        document.getElementById('showArchivedSurveysToggle').addEventListener('change', (event) => {
            isShowingArchivedSurveys = event.target.checked;
            loadSurveys(); // Reload the survey list with the new setting
        });

        
    }

    function openCreateSurveyModal() {
        document.getElementById('createSurveyModal').classList.remove('hidden'); // Modal Management
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

    function handleShowArchivedToggleOffices(event) {
        isShowingArchivedOffices = event.target.checked; // Handle toggle for showing archived offices
        loadOffices();
    }

    function openOfficeEditModal(officeId) { 
        const office = offices.find(o => o.id == officeId);
        if (!office) {
            console.error('Office not found for ID:', officeId);
            alert('Could not find the office to edit.');
            return;
        }

        document.getElementById('editOfficeId').value = office.id;
        document.getElementById('editOfficeName').value = office.name;
        document.getElementById('editOfficeCode').value = office.code;
        document.getElementById('editOfficeDescription').value = office.description || '';

        document.getElementById('editOfficeModal').classList.remove('hidden');
    }

        function closeEditOfficeModal() {
        document.getElementById('editOfficeModal').classList.add('hidden');
        document.getElementById('editOfficeForm').reset();
    }

    function openAddServiceModal() {
        document.getElementById('addServiceModal').classList.remove('hidden');
    }

    function closeAddServiceModal() {
        document.getElementById('addServiceModal').classList.add('hidden');
        document.getElementById('addServiceForm').reset();
    }

    function closeEditServiceModal(){
        document.getElementById('editServiceModal').classList.add('hidden');
        document.getElementById('editServiceForm').reset();
    }


    function handleShowArchivedServices(event) {
        isShowingArchivedServices = event.target.checked; // called when the checkbox is toggled
        loadServices();
    }

    function switchTab(tab) { // Tab Management
        currentTab = tab;
        
        document.querySelectorAll('.tab-button').forEach(btn => { // Update tab buttons
            btn.classList.remove('active', 'border-jru-blue', 'text-jru-blue');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        
        document.getElementById(tab + 'Tab').classList.add('active', 'border-jru-blue', 'text-jru-blue');
        document.getElementById(tab + 'Tab').classList.remove('border-transparent', 'text-gray-500');
        
        document.querySelectorAll('.tab-content').forEach(content => { 
            content.classList.add('hidden');  // Show/hide content
        });
        
        document.getElementById(tab + 'Content').classList.remove('hidden');
        
        if (tab === 'offices') {
            renderOffices();
            renderServices();
        }
    }

    async function loadOffices() {
        console.log(`Loading offices... (Archived view: ${isShowingArchivedOffices})`); // Data Loading Functions
        
        let apiUrl;

        if (isShowingArchivedOffices){
                apiUrl = 'api/offices.php?show_archived_offices=true';
        } else {
                apiUrl = 'api/offices.php';
        }

        try {
        
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                cache: 'no-cache'
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('API response:', result);
            
            if (result.success) {
                offices = result.data;
                console.log('Offices loaded successfully:', offices.length, 'offices'); // Render offices and populate selects
                
                renderOffices();
                populateOfficeSelects();
            } else {
                console.error('API returned error:', result.message);
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Error loading offices:', error);
            
            const container = document.getElementById('officesList'); //Error handling for UI
            if (container) {
                container.innerHTML = `
                    <div class="text-red-500 p-4 bg-red-50 rounded-lg">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Failed to load ofCheck consfices: ${error.message}
                        <br><small>ole for details</small>
                        <button onclick="loadOffices()" class="mt-2 bg-blue-500 text-white px-3 py-1 rounded text-sm">
                            Retry
                        </button>
                    </div>
                `;
            }
        }
    }


    async function loadServices() {
        const officeId = document.getElementById('officeFilter').value;
        
        console.log(`Loading services... (Archived: ${isShowingArchivedServices}, Office ID: ${officeId})`);
        
        let apiUrl = 'api/services.php?';  // base API URL

        const params = new URLSearchParams(); // Use URLSearchParams to build the query string cleanly
        if (isShowingArchivedServices) {
            params.append('show_archived_services', 'true');
        }
        if (officeId) {
            params.append('office_id', officeId);
        }
        
        apiUrl += params.toString(); // Add the parameters to the URL

        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                cache: 'no-cache'
            }); // ---: Fetch data and handle UI updates (inside try/catch)

            console.log('Service Response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('Service API response:', result);

            if (result.success) {
                services = result.data;
                console.log('Services loaded successfully:', services.length, 'services');

                renderServices();  // After loading, render the new list
            } else {
                console.error('API returned error while loading services:', result.message);
                throw new Error(result.message);
            }

        } catch (error) {
            console.error('Error in loadServices function:', error);
            
            const container = document.getElementById('servicesList');  // show an error in the UI
            if (container) {
                container.innerHTML = `
                    <div class="text-red-500 p-4 bg-red-50 rounded-lg text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Failed to load services.
                        <button onclick="loadServices()" class="mt-2 bg-blue-500 text-white px-3 py-1 rounded text-sm">
                            Retry
                        </button>
                    </div>
                `;
            }
        }
    }

    async function loadSurveys() {
        
        console.log(`Attempting to load surveys... (Archived view: ${isShowingArchivedSurveys})`);  // We now use the global state variable to log the current mode.
        
        const apiUrl = isShowingArchivedSurveys 
            ? 'api/surveys.php?show_archived=true' 
            : 'api/surveys.php'; // We build the API URL dynamically based on the toggle's state.

        try {
            const response = await fetch(apiUrl);  // We use the new 'apiUrl' variable in the fetch call.

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result.success && Array.isArray(result.data)) {
                surveys = result.data;
            } else {
                console.error("API response was not successful or data is not an array:", result);
                surveys = [];
            }

        } catch (error) {
            console.error('Error loading surveys:', error.message);
            surveys = [];
        }
        
        renderSurveys(); // The render call remains at the end, which is perfect.
    }
        
        
    function renderSurveys() {
        const tbody = document.getElementById('surveysTableBody');

        if (!surveys || surveys.length === 0) {
            let emptyMessage = isShowingArchivedSurveys 
                ? 'No archived surveys found.' 
                : 'No active surveys found.';  // 1. Handle the "empty state" for both active and archived views.
            
            let emptyStateButton = !isShowingArchivedSurveys 
                ? `<button onclick="openCreateSurveyModal()" class="mt-4 bg-jru-blue text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Survey
                </button>` 
                : ''; // The "Create Survey" button should only show in the "empty active" state.

            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-poll text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium">${emptyMessage}</p>
                            ${emptyStateButton}
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = surveys.map(survey => { // 2. If there are surveys, build the table rows.
            
            const officeName = survey.office_name || 'Unknown Office';  // Prepare the data for display
            const serviceName = survey.service_name || 'Unknown Service';
            // --- DYNAMIC BUTTON LOGIC (This part was correct) ---
            const actionButtons = isShowingArchivedSurveys
                ? `<!-- Archived View Buttons -->
                <button onclick="reactivateSurvey(${survey.id})" class="text-gray-400 hover:text-green-600" title="Reactivate Survey">
                    <i class="fas fa-undo-alt"></i>
                </button>
                <button onclick="permanentlyDeleteSurvey(${survey.id})" class="text-gray-400 hover:text-red-600" title="Permanently Delete">
                    <i class="fas fa-trash-alt"></i>
                </button>`
                : `<!-- Active/Draft View Buttons -->
                <button onclick="editSurvey(${survey.id})" class="text-gray-400 hover:text-jru-blue" title="Edit Survey">
                    <i class="fas fa-edit"></i>
                </button>
                <!-- View Result 
                    <button onclick="viewSurvey(${survey.id})" class="text-gray-400 hover:text-green-600" title="View Results">
                    <i class="fas fa-chart-bar"></i>
                </button> -->
                <button onclick="getSurveyLink(${survey.id})" class="text-gray-400 hover:text-blue-600" title="Get Shareable Link">
                    <i class="fas fa-link"></i>
                </button>
                <button onclick="deleteSurvey(${survey.id})" class="text-gray-400 hover:text-red-600" title="Archive Survey">
                    <i class="fas fa-box-archive"></i>
                </button>`;

            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${survey.title}</div>
                        <div class="text-sm text-gray-500">${survey.description || 'No description'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${officeName}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${serviceName}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(survey.status)}">
                            ${survey.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">${survey.response_count || 0}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(survey.created_at)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-3">${actionButtons}</div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function reactivateSurvey(surveyId) {
        try {
            await showConfirmationModal({
                title: 'Reactivate Survey',
                message: 'This will move the survey back to your main list as a draft, ready for editing.',
                actionText: 'Yes, Reactivate',
                style: 'info' 
            });

            const url = `api/surveys.php?id=${surveyId}`; // The URL only contains the ID.
            
            const response = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({     // Send the body that the PHP API expects for this action.
                    action: 'change_status', 
                    status: 'draft' // Reactivating always sets the status back to 'draft'.
                })
            });
            
            const result = await response.json();

            if (result.success) {
                showToastNotification('Survey reactivated!', 'success');
                loadSurveys(); // Reload the surveys list. The reactivated item will now be in the main list.
            } else {
                showToastNotification(result.message, 'error');
            }
        } catch (error) {
            if (error) console.error("Reactivation Error:", error);
        }
    }

    async function permanentlyDeleteSurvey(surveyId) {
        try {
            await showConfirmationModal({   // This confirmation MUST be very clear about the danger
                title: 'PERMANENTLY DELETE SURVEY',
                message: 'This action is irreversible. All survey data and associated responses will be deleted forever. Are you absolutely sure?',
                actionText: 'Yes, Delete Forever',  //  destructive: true
            });

            const response = await fetch(`api/permanent-delete-survey.php?id=${surveyId}`, { // Use a different API endpoint for this dangerous action
                method: 'DELETE'
            });
            const result = await response.json();
            if (result.success) {
                showToastNotification('Survey permanently deleted.', 'success');
                loadSurveys(); // Reload the list
            } else {
                showToastNotification(result.message, 'error');
            }
        } catch(error) { if(error) console.error(error); }
    }


    function getSurveyLink(surveyId) {
        // window.location.origin gives you the base URL,  window.location.pathname.split('/').slice(0, -1).join('/') gets the current directory path
        const basePath = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
        const surveyUrl = `${basePath}/take-survey.php?id=${surveyId}`;
        window.prompt("Copy this link to share the survey:", surveyUrl); // Use a prompt box to show the link and make it easy to copy
    }

    function editSurvey(surveyId) {
        
        console.log(`Preparing to edit survey. Redirecting with ID: ${surveyId}`);
        const url = `survey-builder.php?survey_id=${surveyId}`; // Build the URL. The parameter name MUST BE 'survey_id'.  The query string MUST START with a '?'.

        window.location.href = url; // 3. Redirect the user.
    }

    function renderOffices() {
        const container = document.getElementById('officesList');
        if (offices.length === 0) {
            container.innerHTML = `<p class="text-gray-500 text-center py-4">${isShowingArchivedOffices ? 'No archived offices found.' : 'No active offices found.'}</p>`;
            return;
        }

        container.innerHTML = offices.map(office => {
            // --- DYNAMIC BUTTON LOGIC ---
            const buttons = isShowingArchivedOffices
                ? `<!-- Archived View Buttons -->
                <button onclick="reactivateOffice(${office.id})" class="text-gray-400 hover:text-green-600" title="Reactivate Office">
                    <i class="fas fa-undo-alt"></i>
                </button>
                <button onclick="permanentlyDeleteOffice(${office.id})" class="text-gray-400 hover:text-red-600" title="Permanently Delete">
                    <i class="fas fa-trash-alt"></i>
                </button>`
                : `<!-- Active View Buttons -->
                <button onclick="openOfficeEditModal(${office.id})" class="text-blue-600 hover:text-blue-800" title="Edit Office">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteOffice(${office.id})" class="text-orange-600 hover:text-orange-800" title="Archive Office">
                    <i class="fas fa-box-archive"></i> <!-- Using the archive icon -->
                </button>`;

            return `
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div>
                        <div class="font-medium text-gray-900">${office.name}</div>
                        <div class="text-sm text-gray-500">Code: ${office.code}</div>
                    </div>
                    <div class="flex space-x-4">${buttons}</div>
                </div>
            `;
        }).join('');
    }

    function renderServices() {
        const container = document.getElementById('servicesList');

        if (services.length === 0) { // // The services array is now pre-filtered by the loadServices() API call, A simplified check
                container.innerHTML = `<p class="text-gray-500 text-center py-4">${isShowingArchivedServices ? 'No archived services found.' : 'No active services found.'}</p>`;
                return;
            }

            container.innerHTML = services.map(service => {
                const buttons = isShowingArchivedServices // --- DYNAMIC BUTTON LOGIC ---
                    ? `<!-- Archived View Buttons -->
                    <button onclick="reactivateService(${service.id})" class="text-gray-400 hover:text-green-600" title="Reactivate Service">
                        <i class="fas fa-undo-alt"></i>
                    </button>
                    <button onclick="permanentlyDeleteService(${service.id})" class="text-gray-400 hover:text-red-600" title="Permanently Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>`
                    : `<!-- Active View Buttons -->
                    <button onclick="openServiceEditModal(${service.id})" class="text-blue-600 hover:text-blue-800" title="Edit Service">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteService(${service.id})" class="text-orange-600 hover:text-orange-800" title="Archive Service">
                        <i class="fas fa-box-archive"></i>
                    </button>`;

                return `
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900">${service.name}</div>
                            <div class="text-sm text-gray-500">${service.office_name || 'Unknown Office'}</div>
                        </div>
                        <div class="flex space-x-4">${buttons}</div>
                    </div>
                `;
            }).join('');
    }
        
    async function reactivateOffice(officeId) {
        try {
                await showConfirmationModal({
                    title: 'Reactivate Office',
                    message: 'This will make the office available again for new surveys and services.',
                    actionText: 'Yes, Reactivate'
                });

                const response = await fetch(`api/offices.php?id=${officeId}`, { // The user confirmed. Now send the request.
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'reactivate' })  // This body is crucial. It sends the 'reactivate' signal to the API.
                });
                
                const result = await response.json();

                if (result.success) {
                    showToastNotification('Office reactivated successfully!', 'success');
                    loadOffices();   // reload the offices list to see the change. Since we are in the archived view, the reactivated office will disappear from this list.
                } else {
                    showToastNotification(result.message, 'error');
                }
            } catch (error) {
                if(error) console.error("Reactivation error:", error); // This handles the user clicking "Cancel"
        }
    }

        async function permanentlyDeleteOffice(officeId) {
            try {
                await showConfirmationModal({
                    title: 'PERMANENTLY DELETE OFFICE',
                    message: 'WARNING: This action is irreversible and cannot be undone. This will only succeed if the office has no services linked to it.',
                    actionText: 'Delete Forever'
                });

                const response = await fetch(`api/permanent-delete-office.php?id=${officeId}`, { // The user confirmed. Now call our NEW, dedicated API endpoint.
                    method: 'DELETE'
                });
                
                const result = await response.json();

                if (result.success) {
                    showToastNotification('Office permanently deleted.', 'success');  // Reload the office list. The item will be gone from the archived view.
                    loadOffices(); 
                } else {
                    showToastNotification(result.message, 'error');  // This will display the helpful error message from the API, // e.g., "Cannot delete... it still has services linked to it."
                }

            } catch (error) {
                if (error) {
                    console.error("Permanent delete error:", error); // This handles the user clicking "Cancel" on the confirmation modal.
                }
            }
        }

        async function reactivateService(serviceId) {
            try {
                await showConfirmationModal({
                    title: 'Reactivate Service',
                    message: 'This will make the service available again for new surveys.',
                    actionText: 'Yes, Reactivate',
                    style: 'info'
                });

                const response = await fetch(`api/services.php?id=${serviceId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'reactivate' })
                });
                const result = await response.json();

                if (result.success) {
                    showToastNotification('Service reactivated!', 'success');
                    loadServices(); // Reload the list
                } else {
                    showToastNotification(result.message, 'error');
                }
            } catch (error) { if (error) console.error(error); }
        }

        async function permanentlyDeleteService(serviceId) {
            try {
                await showConfirmationModal({
                    title: 'PERMANENTLY DELETE SERVICE',
                    message: 'WARNING: This is irreversible. It may fail if surveys are still linked to this service.',
                    actionText: 'Delete Forever',
                });

                const response = await fetch(`api/permanent-delete-service.php?id=${serviceId}`, { // We will create a new, dedicated API file for this dangerous action
                    method: 'DELETE'
                });
                const result = await response.json();

                if (result.success) {
                    showToastNotification('Service permanently deleted.', 'success');
                    loadServices(); // Reload the list
                } else {
                    showToastNotification(result.message, 'error');
                }
            } catch (error) { 
                if (error) console.error("Permanent deletion of service cancelled or failed:", error);
            }
        }

        async function handleCreateSurvey(e) { // Form Handlers
            e.preventDefault();
            
            const formData = { //Gets the Ids of the selected office and service
                title: document.getElementById('newSurveyTitle').value,
                description: document.getElementById('newSurveyDescription').value,
                office_id: document.getElementById('newSurveyOffice').value,
                service_id: document.getElementById('newSurveyService').value
            };

            const params = new URLSearchParams(formData); // Creates a url query strings title=...deescription=...office_id=...service_id=...
            window.location.href = `survey-builder.php?${params.toString()}`;  //redirects the user tothe builder page with all the data in the URL
        }

        async function handleAddOffice(e) {
            e.preventDefault();
            
            const newOffice = {
                name: document.getElementById('newOfficeName').value,
                code: document.getElementById('newOfficeCode').value,
                description: document.getElementById('newOfficeDescription').value
            };

            try {
                const response = await fetch("api/offices.php", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(newOffice)
                });

                const result = await response.json();
                console.log('Server response object:', result);
                if (result.success === true || result.success == 1) {
                   showToastNotification("Office added successfully!", 'success');

                    await loadOffices();
                    populateOfficeSelects();
                    closeAddOfficeModal();

                } else {
                     showToastNotification("Error adding office: " + result.message);
                }
            }   catch (error) {
                console.error('Error submitting form:', error);
                showToastNotification('A network error occured. ', 'error'); 
            }
            
        }


        async function handleUpdateOffice(e){
            e.preventDefault();

            const officeId = document.getElementById('editOfficeId').value;
            const updatedOffice = {
                name: document.getElementById('editOfficeName').value,
                code: document.getElementById('editOfficeCode').value,
                description: document.getElementById('editOfficeDescription').value
            };
           
            try {  //Fetch the data of office with the update method 
                const response = await fetch(`api/offices.php?id=${officeId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(updatedOffice)
                });

                const result = await response.json();

                if (result.success) {
                    showToastNotification('Office updated successfully!');
                    closeEditOfficeModal();
                    await loadOffices();
                } else {
                    alert('Error updating office: ' + result.message);
                }

            } catch(error) {
                console.error('Error updating office:', error);
                alert('An error occurred while updating the office.');
            }
        }

       
        async function deleteOffice(officeId) {
            try {
                await showConfirmationModal({
                    title: 'Archive Office',
                     message: 'This will hide the office from lists. You can reactivate it later.',
                    actionText: 'Yes, Archive', // destructive: true
                });

                const response = await fetch(`api/offices.php?id=${officeId}`, { // This fetch call goes to the OFFICES api
                    method: 'DELETE'
             });
        
             const result = await response.json();

                if (result.success) {
                    showToastNotification('Office archived successfully!', 'success');
                    await loadOffices(); // It reloads OFFICES
                } else {
                    showToastNotification(result.message, 'error');
                }

                }  catch (error) {
                       
                        if (error) { // Check if 'error' is a real error, not just a cancel click
                            console.error('Error archiving office:', error);
                            showToastNotification('A network error occurred.', 'error');
                        } else {
                            console.log('Office archival was cancelled by the user.'); // This is what happens on "Cancel" click. Do nothing or show a message.
                        }
                }
        }

        
         async function handleAddService(e) {
            e.preventDefault();
            
            const newService = {
                office_id: document.getElementById('newServiceOffice').value,
                name: document.getElementById('newServiceName').value,
                code: document.getElementById('newServiceCode').value,
                description: document.getElementById('newServiceDescription').value
            };

            if (!newService.office_id) {
                showToastNotification('Please select an office for the service.', 'error'); //check if an office is selected
                return;
            }

            try {
                const response = await fetch('api/services.php', {
                    method:'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(newService)
                });

                const result = await response.json();
                if (result.success) {
                    showToastNotification('Service added successfully!');
                    
                    await loadServices();
                    closeAddServiceModal();
                } else {
                     showToastNotification(result.message || 'An error occurred.', 'error');
                }

            } catch (error) {
                console.error('Error adding service: ', error);
                showToastNotification('A network error occured. ', 'errror');
            }
        }

        function openServiceEditModal(serviceId) {
            console.log("Opening edit modal for service ID:", serviceId); // A quick console log to confirm the function is being called

            const service = services.find(s => s.id == serviceId); // Find the service in our global 'services' array
            
            if (!service) { // Safety check in case the service isn't found
                console.error("Could not find service with ID:", serviceId);
                showToastNotification('Error: Could not find the service to edit.', 'error');
                return;
            }
            document.getElementById('editServiceId').value = service.id; 
            document.getElementById('editServiceName').value = service.name;
            document.getElementById('editServiceCode').value = service.code;
            document.getElementById('editServiceDescription').value = service.description || '';
            document.getElementById('editServiceOfficeName').value = service.office_name || 'Unknown Office'; // 'office_name' is the special field we get from API's JOIN query
            document.getElementById('editServiceModal').classList.remove('hidden');  // Finally, show the modal by removing the 'hidden' class
        }

        
        async function handleUpdateService(e) {
            e.preventDefault();
            const serviceId = document.getElementById('editServiceId').value;
            const updatedService = {
                name: document.getElementById('editServiceName').value,
                code: document.getElementById('editServiceCode').value,
                description: document.getElementById('editServiceDescription').value
            };

            try {
                const response = await fetch(`api/services.php?id=${serviceId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(updatedService)
                });
                const result = await response.json();
                if (result.success) {
                    showToastNotification('Service updated successfully!', 'success');
                    closeEditServiceModal();
                    await loadServices();
                } else {
                    showToastNotification(result.message || 'Update failed.', 'error');
                }
            } catch (error) {
                showToastNotification('A network error occurred.', 'error');
            }
        }

        async function deleteService(serviceId) {  // this function for archiving (soft-deleting)
            try {
                await showConfirmationModal({
                    title: 'Archive Service',
                    message: 'Are you sure? This will hide the service from active use.',
                    actionText: 'Yes, Archive'
                });
                
                const response = await fetch(`api/services.php?id=${serviceId}`, { method: 'DELETE' });
                const result = await response.json();

                if (result.success) {
                    showToastNotification('Service archived successfully!', 'success');
                    await loadServices();
                } else {
                    showToastNotification(result.message || 'Archive failed.', 'error');
                }
            } catch (error) {
                if(error) { // This catch block handles the user clicking "Cancel" on the modal // Only show network error if it's a real error
                    console.error("Error during service archival:", error);
                    showToastNotification('A network error occurred.', 'error');
                }
            }
        }

        async function reactivateService(serviceId) { // ** ADD ** this function for reactivating
            try {
                   await showConfirmationModal({
                    title: 'Reactivate Service',
                    message: 'This will make the service available again for new surveys.',
                    actionText: 'Yes, Reactivate',
                    style: 'info' // Use the safe, blue style for the button and icon
                });
                const response = await fetch(`api/services.php?action=reactivate&id=${serviceId}`, { 
                     method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'reactivate' })
                 });

                const result = await response.json();
                if (result.success) {
                    showToastNotification('Service reactivated successfully!', 'success');
                    await loadServices(); // Reloads the list, removing the item from the archived view
                } else {
                    showToastNotification(result.message || 'Reactivation failed.', 'error');
                }
            } catch (error) {
               if (error) console.error("Reactivation of service cancelled or failed:", error);
            }
        }
     
        
        async function deleteService(serviceId) {
            try {
                await showConfirmationModal({
                    title: 'Archive Service',
                    message: 'Are you sure you want to archive this office? It will be hidden from active use.',
                    actionText: 'Yes, Archive', // destructive: true
                });

                const response = await fetch(`api/services.php?id=${serviceId}`, {  // This fetch call goes to the SERVICES api
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showToastNotification('Service archived successfully!', 'success');
                    await loadServices(); // It reloads SERVICES
                    renderServices();     // It re-renders the SERVICES list
                } else {
                    showToastNotification(result.message, 'error');
                }
            } catch (error) {
                if (error) {
                    console.error('Error archiving service:', error);
                    showToastNotification('A network error occurred.', 'error');
                } else {
                    console.log('Service archival was cancelled.');
                }
            }
        }

       
        function populateOfficeSelects() { // Helper Functions
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
            if(!dateString){
                return 'N/A'; //Return when dateString is null or undefined
            }

            const date = new Date(dateString + 'Z');

            if (isNaN(date.getTime())) { //check if date is invalid
                return 'Invalid Date';
            }

            return date.toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'short',
                day:'numeric'
            });
        }

        function editSurvey(surveyId) {
            const url = `survey-builder.php?survey_id=${surveyId}`;
            window.location.href = url;
        }


        async function deleteSurvey(surveyId) {
            try {

                await showConfirmationModal({
                    title: 'Archive Survey',
                    message: 'This will hide the survey from the main list and stop new responses.',
                    actionText: 'Yes, Archive', //  destructive: true //Make button red
                });

                const response = await fetch(`api/surveys.php?id=${surveyId}`, { //if the user confirms, we proceed with the archival
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showToastNotification("Survey archived successfully!");
                    loadSurveys(); //reload survey lists
                } else {
                    showToastNotification(result.message || "An error occured. Failed to archive.", "error");
                }

            } catch (error) {

                if (error) {
                    console.error("Error archiving survey:", error);
                    showToastNotification("A network error occured.", "error");
                } else {
                    console.log("Survey archival was cancelled by the user.");
                }

            }
        }
        
        function getSurveyLink(surveyId) {
            const basePath = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
            const surveyUrl = `${basePath}/take-survey.php?id=${surveyId}`;
            window.prompt("Copy this link to share the survey:", surveyUrl);
        }

        const confirmationModal = document.getElementById('confirmationModal');
        const confirmTitle = document.getElementById('confirmationTitle');
        const confirmMessage = document.getElementById('confirmationMessage');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');

        function showConfirmationModal({ title, message, actionText = 'Confirm' }) {
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmTitle = document.getElementById('confirmationTitle');  // These get the elements from the modal's HTML
        const confirmMessage = document.getElementById('confirmationMessage');
        const confirmActionBtn = document.getElementById('confirmActionBtn');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');

    
        if (!confirmationModal) { // Safety check in case the HTML doesn't exist
            if (window.confirm(`${title}\n\n${message}`)) { // If the modal HTML isn't on the page, fall back to the basic browser confirm
                return Promise.resolve(); // User clicked "OK"
            } else {
                return Promise.reject();  // User clicked "Cancel"
            }
        }

            return new Promise((resolve, reject) => {  // This returns a Promise, which lets us use 'await'
                confirmTitle.textContent = title; // Set the text for this specific confirmation
                confirmMessage.textContent = message;
                confirmActionBtn.textContent = actionText;

                confirmationModal.classList.remove('hidden'); // Show the modal

                confirmActionBtn.onclick = () => {
                    confirmationModal.classList.add('hidden'); // When the main action button is clicked, close the modal and resolve the promise (succeed)
                    resolve();
                };

                confirmCancelBtn.onclick = () => {
                    confirmationModal.classList.add('hidden'); // When the cancel button is clicked, close the modal and reject the promise (fail/cancel)
                    reject(); 
                };
            });
        }


        let toastTimer; 

        function showToastNotification(message, type = 'success') {
            const toast = document.getElementById('toastNotification');
            const toastIcon = document.getElementById('toastIcon');
            const toastMessage = document.getElementById('toastMessage');

            clearTimeout(toastTimer);
           
            toastMessage.textContent = message;
            
            if (type === 'success') {
                toast.classList.remove('bg-red-500');
                toast.classList.add('bg-green-500');
                toastIcon.className = 'fas fa-check-circle mr-3 text-xl'; // Success icon
            } else { // 'error'
                toast.classList.remove('bg-green-500');
                toast.classList.add('bg-red-500');
                toastIcon.className = 'fas fa-exclamation-circle mr-3 text-xl'; // Error icon
            }

            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.remove('opacity-0');
            }, 10);

           
            const duration = type === 'success' ? 3000 : 5000;
            
            toastTimer = setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 300);
            }, duration);
        }
 

        function handleSearch() {
            const query = document.getElementById('searchInput').value.toLowerCase(); // Implement search functionality
        }
