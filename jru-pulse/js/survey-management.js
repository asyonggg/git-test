        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
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

                //Itago ang mga tekstual na elemento
                menuTexts.forEach(text => {
                    text.style.opacity = '0';
                    setTimeout(() => {
                        text.style.display = 'none';
                    }, 150);
                });

                //itago ang logo
                logoContainer.style.opacity ='0';
                setTimeout(() => {
                   logoContainer.style.display = 'none';
                }, 150);

            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');

                //Ipakita ang mga tekstual na elemento
                setTimeout(() => {
                    menuTexts.forEach(text => {
                        text.style.display = 'block';
                        setTimeout(() => {
                             text.style.opacity = '1';
                        }, 50);
                    });

                //Ipakita ang logo
                logoContainer.style.display = 'flex';
                setTimeout(() => {
                    logoContainer.style.opacity = '1';
                }, 50);
            }, 150);
        }
    }
        
    // Add the existing sidebar toggle event listener
    sidebarToggle.addEventListener('click', toggleSidebar);
        
        // Global state
        let offices = [];
        let services = [];
        let isShowingArchivedOffices = false;
        let isShowingArchivedServices = false;
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

            // Office selection for services
            const newSurveyOffice = document.getElementById('newSurveyOffice');
            if (newSurveyOffice) {
                newSurveyOffice.addEventListener('change', handleOfficeChange);
            }
            
            const officeFilter = document.getElementById('officeFilter');
            if (officeFilter) {
                officeFilter.addEventListener('change', filterServices);
            }

            // Search
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', handleSearch);
            }

            //Show archived offices toggle
            const showArchivedToggleOffices = document.getElementById('showArchivedToggleOffices');
            if (showArchivedToggleOffices) {
                showArchivedToggleOffices.addEventListener('change', handleShowArchivedToggleOffices);
            }

            //Show archived services toggle - 1st
            const showArchivedToggleServices = document.getElementById('showArchivedToggleServices');
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

        // Handle toggle for showing archived offices
        function handleShowArchivedToggleOffices(event) {
           isShowingArchivedOffices = event.target.checked;
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

      
        // called when the checkbox is toggled
        function handleShowArchivedServices(event) {
            isShowingArchivedServices = event.target.checked;
            loadServices();
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
            console.log(`Loading offices... (Archived view: ${isShowingArchivedOffices})`);
         
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
                    console.log('Offices loaded successfully:', offices.length, 'offices');
                    
                    // Render offices and populate selects
                    renderOffices();
                    populateOfficeSelects();
                } else {
                    console.error('API returned error:', result.message);
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error loading offices:', error);
                
                //Error handling for UI
                const container = document.getElementById('officesList');
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
            
            // base API URL
            let apiUrl = 'api/services.php?';

            // Use URLSearchParams to build the query string cleanly
            const params = new URLSearchParams();
            if (isShowingArchivedServices) {
                params.append('show_archived_services', 'true');
            }
            if (officeId) {
                params.append('office_id', officeId);
            }
            
            // Add the parameters to the URL
            apiUrl += params.toString();

            // ---: Fetch data and handle UI updates (inside try/catch)
            try {
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    cache: 'no-cache'
                });

                console.log('Service Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('Service API response:', result);

                if (result.success) {
                    services = result.data;
                    console.log('Services loaded successfully:', services.length, 'services');

                    // After loading, render the new list
                    renderServices();
                } else {
                    console.error('API returned error while loading services:', result.message);
                    throw new Error(result.message);
                }

            } catch (error) {
                console.error('Error in loadServices function:', error);
                // show an error in the UI
                const container = document.getElementById('servicesList');
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
            console.log("Attempting to load surveys...");
            try {
                //fetch data from Survey api
                const response = await fetch('api/surveys.php');

                if (!response.ok) {
                    // Will correctly throw an error for a 404 response
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const result = await response.json();
                
                if (result.success && Array.isArray(result.data)) {
                    // If successful, set the surveys array to the data
                    surveys = result.data;
                } else {
                    // If the API call succeeded but the data is bad, still reset the array
                    console.error("API response was not successful or data is not an array:", result);
                    surveys = [];
                }

            } catch (error) {
                // If ANY error happens (network, 404, bad JSON), we land here.
                console.error('Error loading surveys:', error.message);
                
                // **This is the most important part.**
                // We GUARANTEE that the surveys array is reset to empty on failure.
                surveys = [];
            }
            
            // By placing it here, it will always be called with the correct state of the surveys array.
            renderSurveys();
        }
        
       
        function renderSurveys() {
            const tbody = document.getElementById('surveysTableBody');
            
            if (!surveys || surveys.length === 0) {
                // This "empty state" logic is correct and stays.
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-poll text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">No surveys found</p>
                                <p class="text-sm">Create your first survey to get started</p>
                                <button onclick="openCreateSurveyModal()" class="mt-4 bg-jru-blue text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Create Survey
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            //This part render the surveys from databae
            tbody.innerHTML = surveys.map(survey => {
                const officeName = survey.office_name || 'Unknown Office';
                const serviceName = survey.service_name || 'Unknown Service';
                
                return `
                    <tr class="hover:bg-gray-50">
                        <!-- Cell 1: Survey Title & Description -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${survey.title}</div>
                            <div class="text-sm text-gray-500">${survey.description || 'No description'}</div>
                        </td>
                        
                        <!-- Cell 2: Office Name -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${officeName}</td>
                        
                        <!-- Cell 3: Service Name -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${serviceName}</td>
                        
                        <!-- Cell 4: Status Badge -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(survey.status)}">
                                ${survey.status}
                            </span>
                        </td>
                        
                        <!-- Cell 5: Responses Count -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">${survey.response_count || 0}</td>
                        
                        <!-- Cell 6: Created Date -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(survey.created_at)}</td>
                        
                        <!-- Cell 7: Action Icons -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <button onclick="editSurvey(${survey.id})" class="text-gray-400 hover:text-jru-blue" title="Edit Survey">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="viewSurvey(${survey.id})" class="text-gray-400 hover:text-green-600" title="View Results">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                                <button onclick="getSurveyLink(${survey.id})" class="text-gray-400 hover:text-blue-600" title="Get Shareable Link">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button onclick="deleteSurvey(${survey.id})" class="text-gray-400 hover:text-red-600" title="Archive Survey">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function getSurveyLink(surveyId) {
            // Construct the full URL to the take-survey page
            // window.location.origin gives you the base URL (e.g., http://localhost)
            // window.location.pathname.split('/').slice(0, -1).join('/') gets the current directory path
            const basePath = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
            const surveyUrl = `${basePath}/take-survey.php?id=${surveyId}`;
            
            // Use a prompt box to show the link and make it easy to copy
            window.prompt("Copy this link to share the survey:", surveyUrl);
        }

       function editSurvey(surveyId) {
            
            console.log(`Preparing to edit survey. Redirecting with ID: ${surveyId}`);
            
            // 2. Build the URL. The parameter name MUST BE 'survey_id'.
            //    The query string MUST START with a '?'.
            const url = `survey-builder.php?survey_id=${surveyId}`;
            
            // 3. Redirect the user.
            window.location.href = url;
        }

        function renderOffices() {
                const container = document.getElementById('officesList');
                if (offices.length === 0) {
                    container.innerHTML = `<p class="text-gray-500 text-center py-4">${isShowingArchivedOffices ? 'No archived offices found.' : 'No active offices found.'}</p>`;
                    return;
                }

                container.innerHTML = offices.map(office => {
                    // Use the global state variable to decide which buttons to show
                    const buttons = isShowingArchivedOffices
                        ? `<!-- Reactivate button -->
                        <button onclick="reactivateOffice(${office.id})" class="text-green-600 hover:text-green-800" title="Reactivate Office">
                            <i class="fas fa-undo-alt"></i>
                        </button>`
                        : `<!-- Edit and Archive buttons -->
                        <button onclick="openOfficeEditModal(${office.id})" class="text-blue-600 hover:text-blue-800" title="Edit Office">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteOffice(${office.id})" class="text-red-600 hover:text-red-800" title="Archive Office">
                            <i class="fas fa-trash"></i>
                        </button>`;

                    return `
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">${office.name}</div>
                                <div class="text-sm text-gray-500">Code: ${office.code}</div>
                            </div>
                            <div class="flex space-x-4">${buttons}</div>
                        </div>`;
                }).join('');
            }

        function renderServices() {
            const container = document.getElementById('servicesList');
            
            // The services array is now pre-filtered by the loadServices() API call,
            if (services.length === 0) {
                // This line now correctly checks the state to show the right message
                container.innerHTML = `<p class="text-gray-500 text-center py-4">${isShowingArchivedServices ? 'No archived services found.' : 'No active services found.'}</p>`;
                return;
            }

            container.innerHTML = services.map(service => {
                
                // This ternary operator checks the global state variable and chooses the correct set of buttons.
                const buttons = isShowingArchivedServices
                    ? `<!-- We are in archived view, so show the Reactivate button -->
                    <button onclick="reactivateService(${service.id})" class="text-green-600 hover:text-green-800" title="Reactivate Service">
                        <i class="fas fa-undo-alt"></i>
                    </button>`
                    : `<!-- We are in the active view, so show Edit and Archive buttons -->
                    <button onclick="openServiceEditModal(${service.id})" class="text-blue-600 hover:text-blue-800" title="Edit Service">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteService(${service.id})" class="text-red-600 hover:text-red-800" title="Archive Service">
                        <i class="fas fa-trash"></i>
                    </button>`;

                return `
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900">${service.name}</div>
                            <div class="text-sm text-gray-500">${service.office_name || 'Unknown Office'}</div>
                        </div>
                        <div class="flex space-x-4">${buttons}</div>
                    </div>`;
            }).join('');
        }
        
        // Reactivate Office Function
        async function reactivateOffice(officeId) {
            try {
                // Use the PUT request, as we are UPDATING the state of the office
                const response = await fetch(`api/offices.php?action=reactivate&id=${officeId}`, {
                    method: 'PUT'
                });
                const result = await response.json();

                if (result.success) {
                    showToastNotification('Office reactivated successfully!', 'success');
                    // Reload the archived list so the item disappears from it
                    loadOffices(true); 
                } else {
                    showToastNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error reactivating office:', error);
                showToastNotification('A network error occurred.', 'error');
            }
        }


        // Form Handlers
        async function handleCreateSurvey(e) {
            e.preventDefault();
            
            //Gets the Ids of the selected office and service
            const formData = {
                title: document.getElementById('newSurveyTitle').value,
                description: document.getElementById('newSurveyDescription').value,
                office_id: document.getElementById('newSurveyOffice').value,
                service_id: document.getElementById('newSurveyService').value
            };

            // Creates a url query strings title=...deescription=...office_id=...service_id=...
            const params = new URLSearchParams(formData);

            //redirects the user tothe builder page with all the data in the URL
            window.location.href = `survey-builder.php?${params.toString()}`;
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
            //Fetch the data of office with the update method 
            try {
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
                    message: 'Are you sure you want to archive this office? It will be hidden from active use.',
                    actionText: 'Yes, Archive',
                    destructive: true
                });

                // This fetch call goes to the OFFICES api
                const response = await fetch(`api/offices.php?id=${officeId}`, {
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
                            // This is what happens on "Cancel" click. Do nothing or show a message.
                            console.log('Office archival was cancelled by the user.');
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

            //check if an office is selected
            if (!newService.office_id) {
                showToastNotification('Please select an office for the service.', 'error');
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
            // A quick console log to confirm the function is being called
            console.log("Opening edit modal for service ID:", serviceId);

            // Find the service in our global 'services' array
            const service = services.find(s => s.id == serviceId);
            
            // Safety check in case the service isn't found
            if (!service) {
                console.error("Could not find service with ID:", serviceId);
                showToastNotification('Error: Could not find the service to edit.', 'error');
                return;
            }

            // --- Populate the form fields using the IDs from your HTML ---
            document.getElementById('editServiceId').value = service.id;
            document.getElementById('editServiceName').value = service.name;
            document.getElementById('editServiceCode').value = service.code;
            document.getElementById('editServiceDescription').value = service.description || '';
            
            // 'office_name' is the special field we get from our API's JOIN query
            document.getElementById('editServiceOfficeName').value = service.office_name || 'Unknown Office';
            
            // Finally, show the modal by removing the 'hidden' class
            document.getElementById('editServiceModal').classList.remove('hidden');
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

        // ** ADD ** this function for archiving (soft-deleting)
        async function deleteService(serviceId) {
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
                // This catch block handles the user clicking "Cancel" on the modal
                if(error) { // Only show network error if it's a real error
                    console.error("Error during service archival:", error);
                    showToastNotification('A network error occurred.', 'error');
                }
            }
        }

        // ** ADD ** this function for reactivating
        async function reactivateService(serviceId) {
            try {
                const response = await fetch(`api/services.php?action=reactivate&id=${serviceId}`, { method: 'PUT' });
                const result = await response.json();
                if (result.success) {
                    showToastNotification('Service reactivated successfully!', 'success');
                    await loadServices(); // Reloads the list, removing the item from the archived view
                } else {
                    showToastNotification(result.message || 'Reactivation failed.', 'error');
                }
            } catch (error) {
                showToastNotification('A network error occurred.', 'error');
            }
        }
     
        
        async function deleteService(serviceId) {
            try {
                await showConfirmationModal({
                    title: 'Archive Service',
                    message: 'Are you sure you want to archive this office? It will be hidden from active use.',
                    actionText: 'Yes, Archive',
                    destructive: true
                });

                // This fetch call goes to the SERVICES api
                const response = await fetch(`api/services.php?id=${serviceId}`, {
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
            if(!dateString){
                return 'N/A'; //Return when dateString is null or undefined
            }

            const date = new Date(dateString + 'Z');

            //check if date is invalid
            if (isNaN(date.getTime())) {
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

        function viewSurvey(surveyId) {
            window.open(`survey-preview.php?id=${surveyId}`, '_blank');
        }

        async function deleteSurvey(surveyId) {
            try {
                await showConfirmationModal({ 
                      title: 'Archive Survey',
                    message: 'Are you sure? This will go to archive.',
                    actionText: 'Yes, Archive'
                });

                // It sends a DELETE request to the surveys API.
                const response = await fetch(`api/surveys.php?id=${surveyId}`, {
                    method: 'DELETE'
                });
                const result = await response.json();

                if (result.success) {
                    showToastNotification('Survey archived successfully!', 'success');
                    loadSurveys(); // Reload the list
                } else {
                    showToastNotification(result.message, 'error');
                }
            } catch (error) {
                if(error) console.error("Error archiving survey:", error);
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

        // This function returns a Promise that resolves or rejects based on user action
        function showConfirmationModal({ title, message, actionText = 'Confirm', destructive = true }) {
            return new Promise((resolve, reject) => {
                confirmTitle.textContent = title;
                confirmMessage.textContent = message;
                confirmActionBtn.textContent = actionText;

                // Style the action button (e.g., red for destructive, blue for normal)
                if (destructive) {
                    confirmActionBtn.classList.remove('bg-jru-blue', 'hover:bg-blue-800');
                    confirmActionBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                } else {
                    confirmActionBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                    confirmActionBtn.classList.add('bg-jru-blue', 'hover:bg-blue-800');
                }

                confirmationModal.classList.remove('hidden');

                // We use .onclick here and set it to null later to ensure we don't
                // have multiple listeners stacking up on the buttons.
                confirmActionBtn.onclick = () => {
                    confirmationModal.classList.add('hidden');
                    resolve(); // User confirmed
                };

                confirmCancelBtn.onclick = () => {
                    confirmationModal.classList.add('hidden');
                    reject(); // User canceled
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
            const query = document.getElementById('searchInput').value.toLowerCase();
            // Implement search functionality
        }