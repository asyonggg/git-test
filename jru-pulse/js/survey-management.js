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

            // Office/Service management
            const addOfficeBtn = document.getElementById('addOfficeBtn');
            if (addOfficeBtn) {
                addOfficeBtn.addEventListener('click', openAddOfficeModal);
            }
            
            const addServiceBtn = document.getElementById('addServiceBtn');
            if (addServiceBtn) {
                addServiceBtn.addEventListener('click', openAddServiceModal);
            }
            
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
            console.log('Loading offices...');
            try {
            
                const response = await fetch('api/offices.php', {
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
                
                // Show error in the offices list
                const container = document.getElementById('officesList');
                if (container) {
                    container.innerHTML = `
                        <div class="text-red-500 p-4 bg-red-50 rounded-lg">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Failed to load offices: ${error.message}
                            <br><small>Check console for details</small>
                            <button onclick="loadOffices()" class="mt-2 bg-blue-500 text-white px-3 py-1 rounded text-sm">
                                Retry
                            </button>
                        </div>
                    `;
                }
            }
        }


        async function loadServices() {
            try {
                const response = await fetch('api/services.php');
                const result = await response.json();
                if (result.success) {
                    services = result.data;
                    console.log('Services loaded successfully:', services.length, 'services');
                }
            } catch (error) {
                console.error('Error loading services:', error);
                // Use fallback data for demo, id 1-11 are services for offices 1-11, office_id is the id of the office(Foreign key)
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
                    {id: 13, office_id: 5, name: "Classroom/Office Technical Assistance", code: "classroom-tech-support"},
                    {id: 14, office_id: 6, name: "Medical Check-up/Consultation", code: "medical-checkup"},
                    {id: 15, office_id: 6, name: "Dental Check-up/Consultation", code: "dental-checkup"},
                    {id: 16, office_id: 6, name: "Request for medical clearances", code: "request-medical-clearance"},
                    {id: 17, office_id: 7, name: "Request for Good Moral Certificate", code: "request-good-moral"},
                    {id: 18, office_id: 7, name:"Request for Counseling", code: "request-counseling"},
                    {id: 19, office_id: 7, name: "Scholarship Inquiry", code: "scholarship-inquiry"},
                    {id: 20, office_id: 8, name: "Filling of complaint", code: "filling-complaint"},
                    {id: 21, office_id: 8, name: "Request for ID Replacement Form", code: "request-id-replacement-form"},
                    {id: 22, office_id: 8, name: "Request for Admission Admission Slip", code: "request-admission-slip"},
                    {id: 23, office_id: 8, name: "Request for Temporary School ID", code: "request-temporary-school-id"},
                    {id: 24, office_id: 9, name: "Borrowing of Sports Equipment", code: "borrowing-sports-equipment"},
                    {id: 25, office_id: 10, name: "General Inquiries", code: "general-inquiries"},
                    {id: 26, office_id: 11, name: "Request for Vehicle", code: "request-vehicle"},
                    {id: 27, office_id: 11, name: "Facility Maintenance", code: "facility-maintenance"},
                    {id: 28, office_id: 11, name: "Auditorium Reservation", code: "auditorium-reservation"},

                ];
            }
        }

        async function loadSurveys() {
            try {
                const response = await fetch('api/surveys.php');
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
                        <button onclick="openOfficeEditModal(${office.id})" class="text-blue-600 hover:text-blue-800">
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

            try {
                const response = await fetch("api/offices.php", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(newOffice)
                });

                const result = await response.json();
                if (result.success) {
                    showSuccessModal("Office added successfully!");

                    await loadOffices();
                    populateOfficeSelects();
                    closeAddOfficeModal();

                } else {
                    alert("Error adding office: " + result.message);
                }
            }   catch (error) {
                console.error('Error submitting form:', error);
                alert('A network error occurred. Please try again.' + error.message);
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
            message: 'Are you sure you want to archive this office? It will be hidden from all lists but can be recovered by an administrator.',
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
                // 3. If the user clicks "Cancel", the promise rejects, and the code
                // jumps directly to this 'catch' block.
                // We also catch real network errors here.
                if (error) { // Check if 'error' is a real error, not just a cancel click
                    console.error('Error archiving office:', error);
                    showToastNotification('A network error occurred.', 'error');
                } else {
                    // This is what happens on "Cancel" click. Do nothing or show a message.
                    console.log('Office archival was cancelled by the user.');
                }
            }
        }


        async function deleteService(serviceId) {
    try {
        await showConfirmationModal({
            title: 'Archive Service',
            message: 'Are you sure you want to archive this service? It will be hidden from use but can be recovered later.',
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
               showToastNotification('Survey deleted successfully!');
            }
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