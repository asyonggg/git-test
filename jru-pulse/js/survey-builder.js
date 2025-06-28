// Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const logoContainer = document.getElementById('logoContainer');
        const menuTexts = document.querySelectorAll('.menu-text');
        
        let sidebarCollapsed = false;
        
        sidebarToggle.addEventListener('click', function() {
            sidebarCollapsed = !sidebarCollapsed;
            
            if (sidebarCollapsed) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                
                menuTexts.forEach(text => {
                    text.style.opacity = '0';
                    setTimeout(() => {
                        text.style.display = 'none';
                    }, 150);
                });
                
                logoContainer.style.opacity = '0';
                setTimeout(() => {
                    logoContainer.style.display = 'none';
                }, 150);
                
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                
                setTimeout(() => {
                    menuTexts.forEach(text => {
                        text.style.display = 'block';
                        setTimeout(() => {
                            text.style.opacity = '1';
                        }, 50);
                    });
                    
                    logoContainer.style.display = 'flex';
                    setTimeout(() => {
                        logoContainer.style.opacity = '1';
                    }, 50);
                }, 150);
            }
        });


        let currentSurveyId = null;
        let surveyQuestions = [];
        let currentEditingQuestion = null;
        let offices = []; // To store office data from API
        let services = []; // To store service data from API

        let toastTimer; 

        function showToastNotification(message, type = 'success') {
            // Find the toast element on the survey-builder.php page
            const toast = document.getElementById('toastNotification'); 
            
            // Safety check in case the element doesn't exist on this page
            if (!toast) {
                alert(`${type.toUpperCase()}: ${message}`); // Fallback to a simple alert
                return;
            }
            
            const toastIcon = document.getElementById('toastIcon');
            const toastMessage = document.getElementById('toastMessage');

            clearTimeout(toastTimer);
            toastMessage.textContent = message;

            if (type === 'success') {
                toast.classList.remove('bg-red-500');
                toast.classList.add('bg-green-500');
                toastIcon.className = 'fas fa-check-circle mr-3 text-xl';
            } else { // 'error'
                toast.classList.remove('bg-green-500');
                toast.classList.add('bg-red-500');
                toastIcon.className = 'fas fa-exclamation-circle mr-3 text-xl';
            }
            
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.remove('opacity-0'), 10);

            const duration = type === 'success' ? 3000 : 5000;
            
            toastTimer = setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, duration);
        }



        // Default template questions
        const defaultTemplate = [
            {
                id: 1,
                type: 'likert',
                text: 'How satisfied are you with the outcome of the service you received?',
                help: 'Rate your overall satisfaction with the service results',
                required: true,
                title: 'Service Outcome'
            },
            {
                id: 2,
                type: 'likert',
                text: 'How satisfied are you with the speed/timeliness of the service?',
                help: 'Rate how quickly your request was processed',
                required: true,
                title: 'Speed of Service'
            },
            {
                id: 3,
                type: 'likert',
                text: 'How clear and helpful were the instructions and processing procedures?',
                help: 'Rate the clarity of instructions provided',
                required: true,
                title: 'Processing & Instruction'
            },
            {
                id: 4,
                type: 'likert',
                text: 'How would you rate the professionalism and courtesy of the staff?',
                help: 'Rate the behavior and attitude of staff members',
                required: true,
                title: 'Staff Professionalism & Courtesy'
            },
            {
                id: 5,
                type: 'textarea',
                text: 'Please provide any suggestions for improvement or additional comments:',
                help: 'Share your thoughts on how we can improve our services',
                required: false,
                title: 'Suggestions'
            }
        ];


                // DOM Elements (defined once globally)
        const questionsList = document.getElementById('questionsList');
        const surveyPreview = document.getElementById('surveyPreview');
        const questionModal = document.getElementById('questionModal');
        const templateModal = document.getElementById('templateModal');
        
        // Initialize the application
        document.addEventListener('DOMContentLoaded', initializeSurveyBuilder);
        
        async function initializeSurveyBuilder() {
            console.log("Initializing Survey Builder...");

            // Set up all static event listeners first
            setupEventListeners();

            // Load the data needed for the dropdowns
            await loadBuilderDropdowns();
            
            const urlParams = new URLSearchParams(window.location.search);
            const surveyIdFromUrl = urlParams.get('survey_id');

            if (surveyIdFromUrl) {
                // --- EDIT MODE ---
                console.log("Mode: Editing Survey");
                currentSurveyId = surveyIdFromUrl;
                await loadSurveyForEditing(currentSurveyId);
            } else {
                // --- CREATE MODE ---
                console.log("Mode: Creating New Survey");
                // Handle parameters passed from the management page
                const title = urlParams.get('title');
                const officeId = urlParams.get('office_id');
                const serviceId = urlParams.get('service_id');
                
                if (title) document.getElementById('surveyTitle').value = title;
                if (officeId) {
                    document.getElementById('surveyOffice').value = officeId;
                    handleBuilderOfficeChange(); // Load the services for this office
                }
                // This will now work because the services are loaded by the line above
                if (serviceId) {
                    document.getElementById('surveyService').value = serviceId;
                }
                
                surveyQuestions = [...defaultTemplate];
                renderQuestions();
                renderPreview();
            }
            
            // Activate drag-and-drop last
            setupSortable();
        }

        async function loadBuilderDropdowns() {
                console.log("Loading office and service dropdowns...");
                try {
                    const [officesRes, servicesRes] = await Promise.all([
                        fetch('api/offices.php'),
                        fetch('api/services.php')
                    ]);
                    const officesResult = await officesRes.json();
                    const servicesResult = await servicesRes.json();

                    if (officesResult.success) {
                        window.offices = officesResult.data;
                        const officeSelect = document.getElementById('surveyOffice');
                        officeSelect.innerHTML = '<option value="">Select Office</option>';
                        window.offices.forEach(o => {
                            officeSelect.innerHTML += `<option value="${o.id}">${o.name}</option>`;
                        });
                    }
                    if (servicesResult.success) {
                        window.services = servicesResult.data;
                        document.getElementById('surveyService').innerHTML = '<option value="">Select an office first</option>';
                        document.getElementById('surveyService').disabled = true;
                    }
                } catch (error) {
                    console.error("Failed to load dropdown data:", error);
                    showToastNotification("Could not load office/service data.", "error");
                }
         }

         function handleBuilderOfficeChange() {
            const officeId = document.getElementById('surveyOffice').value;
            const serviceSelect = document.getElementById('surveyService');
            
            if (!officeId) {
                serviceSelect.innerHTML = '<option value="">Select an office first</option>';
                serviceSelect.disabled = true;
                return;
            }

            // Filter the services we already loaded
            const relevantServices = window.services.filter(s => s.office_id == officeId);

            serviceSelect.innerHTML = '<option value="">Select Service</option>';
            relevantServices.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                serviceSelect.appendChild(option);
            });
            
            serviceSelect.disabled = false;
        }


        async function loadSurveyForEditing(surveyId) {
            console.log(`Fetching data for survey ID: ${surveyId} to edit.`);
            try {
                const response = await fetch(`api/surveys.php?id=${surveyId}`);
                const result = await response.json();

                if (result.success) {
                    // The API sends back a single object, so we use it directly.
                    const survey = result.data; 
                    
                    // Populate the form fields
                    document.getElementById('surveyTitle').value = survey.title;
                    document.getElementById('surveyOffice').value = survey.office_id;
                    handleBuilderOfficeChange();
                    
                    // We need to wait a tiny moment for the services to populate before setting the value
                    // This is a common and simple way to handle this timing.
                    setTimeout(() => {
                        document.getElementById('surveyService').value = survey.service_id;
                    }, 100);

                    // --- THIS IS THE KEY FIX ---
                    let loadedQuestions = [];
                    // We no longer need JSON.parse. The data is already an object.
                    // We just check if the object and its nested 'questions' array exist.
                    if (survey.questions_json && Array.isArray(survey.questions_json.questions)) {
                        loadedQuestions = survey.questions_json.questions;
                        console.log("Successfully loaded questions from API object:", loadedQuestions);
                    }

                    surveyQuestions = loadedQuestions;
                    
                    if (surveyQuestions.length === 0) {
                        console.log("No valid questions found in survey data.");
                    }
                    
                    // Re-render the UI
                    renderQuestions();
                    renderPreview();

                    showToastNotification("Survey loaded for editing.", "success");
                } else {
                    showToastNotification(result.message, 'error');
                }
            } catch (error) {
                console.error("Error loading survey for editing:", error);
            }
        }

        // Setup event listeners
        function setupEventListeners() {
            // Add question buttons
            document.getElementById('addQuestion').addEventListener('click', () => openQuestionModal());
            document.getElementById('addQuestionBottom').addEventListener('click', () => openQuestionModal());
            // Modal close buttons
            document.getElementById('closeQuestionModal').addEventListener('click', () => closeQuestionModal());
            document.getElementById('cancelQuestion').addEventListener('click', () => closeQuestionModal());
            // Template actions
            document.getElementById('loadTemplate').addEventListener('click', loadTemplate);
            document.getElementById('saveAsTemplate').addEventListener('click', () => openTemplateModal());
            document.getElementById('closeTemplateModal').addEventListener('click', () => closeTemplateModal());
            document.getElementById('cancelTemplate').addEventListener('click', () => closeTemplateModal());
            // Question form
            document.getElementById('questionForm').addEventListener('submit', saveQuestion);
            document.getElementById('templateForm').addEventListener('submit', saveTemplate);
            // Question type change
            document.getElementById('questionType').addEventListener('change', handleQuestionTypeChange);
            document.getElementById('surveyOffice').addEventListener('change', handleBuilderOfficeChange);
            
            // Survey info changes
            ['surveyTitle', 'surveyOffice', 'surveyService'].forEach(id => {
                document.getElementById(id).addEventListener('input', renderPreview);
                document.getElementById(id).addEventListener('change', renderPreview);
            });
            
            // Preview and save buttons
            document.getElementById('previewSurvey').addEventListener('click', previewSurvey);
            document.getElementById('saveDraft').addEventListener('click', saveDraft);
            document.getElementById('publishSurvey').addEventListener('click', publishSurvey);
        }


        async function saveDraft() {
            console.log("Attempting to save survey...");

            const surveyData = {
                title: document.getElementById('surveyTitle').value,
                office_id: document.getElementById('surveyOffice').value,
                service_id: document.getElementById('surveyService').value,
                questions: { questions: surveyQuestions }, // The correct nested structure
                status: 'draft'
            };
            
            if (!surveyData.title || !surveyData.office_id || !surveyData.service_id) {
                showToastNotification("Please provide a title, office, and service.", "error");
                return;
            }
            
            try {
                let response;
                let url;
                let method;

                if (currentSurveyId) {
                    // --- THIS IS THE NEW LOGIC ---
                    // We are UPDATING an existing survey.
                    console.log("This is an UPDATE for survey ID:", currentSurveyId);
                    url = `api/surveys.php?id=${currentSurveyId}`; // Add the ID to the URL
                    method = 'PUT'; // Set the method to PUT
                } else {
                    // This is the CREATE logic, which is already working.
                    console.log("This is a new survey CREATE.");
                    url = 'api/surveys.php';
                    method = 'POST';
                }
                
                response = await fetch(url, {
                    method: method, // Use the dynamic method
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(surveyData)
                });
                
                const result = await response.json();

                if (result.success) {
                    showToastNotification('Survey saved successfully!', 'success');
                    // If we just created a new survey, store its ID.
                    if (result.data && result.data.id) {
                        currentSurveyId = result.data.id;
                    }
                } else {
                    showToastNotification(result.message || 'Failed to save.', 'error');
                }
            } catch (error) {
                console.error("Error saving survey:", error);
                showToastNotification('A network error occurred.', 'error');
            }
        }

        
        async function publishSurvey() {
            //1. Check if the survey has been saved atleast once.
            if (!currentSurveyId) {
               showToastNotification("Please save the survey as a draft before publishing.", "error");
               return;
            }

            //2. Confirm the action with the user.
            try {
                await showConfirmationModal({
                    title: 'Publish Survey',
                    message: 'Are you sure you want to publish this survey? It will be available for responses.',
                    actionText: 'Publish',
                    destructive:false //Use the default blue color for the action button
                });

                //User confirmed. Send the request to the API.
                //Sending a PUT or UPDATE request to the existing survey
                const response = await fetch('api/surveys.php?id=${currentSurveyId}', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'publish' })
                });

                const result = await response.json();

                if (result.success) {
                    showToastNotification('Survey published successfully! The survey is now live', 'success');
                } else {
                    showToastNotification(result.message || "Failed to published successfully.", "error");
                }

            } catch (error) {
                if (errrr) {
                console.error("Error publishing survey:", error);
                showToastNotification('A network error occurred while publishing.', 'error');
                } else {
                    console.error("Publishing cancelled by user.");
                }
            } 
          
        }

        // Render questions in the builder
        function renderQuestions() {
            questionsList.innerHTML = '';
            
            surveyQuestions.forEach((question, index) => {
                const questionElement = createQuestionElement(question, index);
                questionsList.appendChild(questionElement);
            });
        }

        // Create question element
        function createQuestionElement(question, index) {
            const div = document.createElement('div');
            div.className = 'question-item bg-gray-50 border border-gray-200 rounded-lg p-4';
            div.dataset.questionId = question.id;
            
            const typeIcon = getTypeIcon(question.type);
            const typeLabel = getTypeLabel(question.type);
            
            div.innerHTML = `
                <div class="flex items-start space-x-4">
                    <div class="drag-handle text-gray-400 hover:text-gray-600 cursor-grab mt-1">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-500">${index + 1}.</span>
                                <span class="text-sm font-medium text-gray-700">${question.title || 'Question'}</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="${typeIcon} mr-1"></i>
                                    ${typeLabel}
                                </span>
                                ${question.required ? '<span class="text-red-500 text-xs">*</span>' : '<span class="text-gray-400 text-xs">Optional</span>'}
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="editQuestion(${question.id})" class="text-gray-400 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="duplicateQuestion(${question.id})" class="text-gray-400 hover:text-green-600 transition-colors">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button onclick="deleteQuestion(${question.id})" class="text-gray-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">${question.text}</p>
                        ${question.help ? `<p class="text-xs text-gray-500 italic">${question.help}</p>` : ''}
                    </div>
                </div>
            `;
            
            return div;
        }

        // Get type icon
        function getTypeIcon(type) {
            const icons = {
                'likert': 'fas fa-star',
                'text': 'fas fa-font',
                'textarea': 'fas fa-align-left',
                'multiple': 'fas fa-list',
                'checkbox': 'fas fa-check-square',
                'rating': 'fas fa-star-half-alt'
            };
            return icons[type] || 'fas fa-question';
        }

        // Get type label
        function getTypeLabel(type) {
            const labels = {
                'likert': 'Likert Scale',
                'text': 'Text Input',
                'textarea': 'Long Text',
                'multiple': 'Multiple Choice',
                'checkbox': 'Checkbox',
                'rating': 'Star Rating'
            };
            return labels[type] || 'Unknown';
        }

        // Render preview
        function renderPreview() {
            const surveyTitle = document.getElementById('surveyTitle').value;
            const surveyOffice = document.getElementById('surveyOffice').selectedOptions[0].text;
            const surveyService = document.getElementById('surveyService').selectedOptions[0].text;
            
            let previewHTML = `
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="font-semibold text-blue-900 text-sm">${surveyTitle}</h3>
                    <p class="text-xs text-blue-700">${surveyOffice} - ${surveyService}</p>
                </div>
            `;
            
            surveyQuestions.forEach((question, index) => {
                previewHTML += `
                    <div class="mb-4 p-3 border border-gray-200 rounded-lg">
                        <div class="flex items-center mb-2">
                            <span class="text-xs font-medium text-gray-500 mr-2">${index + 1}.</span>
                            <span class="text-xs font-medium text-gray-700">${question.title || 'Question'}</span>
                            ${question.required ? '<span class="text-red-500 text-xs ml-1">*</span>' : ''}
                        </div>
                        <p class="text-xs text-gray-600 mb-2">${question.text}</p>
                        ${renderPreviewInput(question)}
                    </div>
                `;
            });
            
            surveyPreview.innerHTML = previewHTML;
        }

        // Render preview input based on question type
        function renderPreviewInput(question) {
            switch (question.type) {
                case 'likert':
                    return `
                        <div class="flex space-x-1">
                            ${[1,2,3,4,5].map(i => `
                                <div class="w-6 h-6 border border-gray-300 rounded text-xs flex items-center justify-center">${i}</div>
                            `).join('')}
                        </div>
                    `;
                case 'text':
                    return '<div class="w-full h-6 border border-gray-300 rounded bg-gray-50"></div>';
                case 'textarea':
                    return '<div class="w-full h-12 border border-gray-300 rounded bg-gray-50"></div>';
                case 'multiple':
                    return `
                        <div class="space-y-1">
                            ${['Option 1', 'Option 2', 'Option 3'].map(opt => `
                                <div class="flex items-center">
                                    <div class="w-3 h-3 border border-gray-300 rounded-full mr-2"></div>
                                    <span class="text-xs text-gray-600">${opt}</span>
                                </div>
                            `).join('')}
                        </div>
                    `;
                case 'checkbox':
                    return `
                        <div class="space-y-1">
                            ${['Option 1', 'Option 2', 'Option 3'].map(opt => `
                                <div class="flex items-center">
                                    <div class="w-3 h-3 border border-gray-300 rounded mr-2"></div>
                                    <span class="text-xs text-gray-600">${opt}</span>
                                </div>
                            `).join('')}
                        </div>
                    `;
                case 'rating':
                    return `
                        <div class="flex space-x-1">
                            ${[1,2,3,4,5].map(i => `
                                <i class="fas fa-star text-gray-300 text-sm"></i>
                            `).join('')}
                        </div>
                    `;
                default:
                    return '<div class="w-full h-6 border border-gray-300 rounded bg-gray-50"></div>';
            }
        }

        
        // Setup sortable functionality
        function setupSortable() {
            new Sortable(questionsList, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function(evt) {
                    const oldIndex = evt.oldIndex;
                    const newIndex = evt.newIndex;
                    
                    // Reorder questions array
                    const movedQuestion = surveyQuestions.splice(oldIndex, 1)[0];
                    surveyQuestions.splice(newIndex, 0, movedQuestion);
                    
                    // Re-render
                    renderQuestions();
                    renderPreview();
                }
            });
        }

        // Question management functions
        function openQuestionModal(questionId = null) {
            currentEditingQuestion = questionId;
            
            if (questionId) {
                const question = surveyQuestions.find(q => q.id === questionId);
                if (question) {
                    document.getElementById('questionType').value = question.type;
                    document.getElementById('questionText').value = question.text;
                    document.getElementById('questionHelp').value = question.help || '';
                    document.querySelector(`input[name="required"][value="${question.required}"]`).checked = true;
                }
            } else {
                document.getElementById('questionForm').reset();
            }
            
            handleQuestionTypeChange();
            questionModal.classList.remove('hidden');
        }

        function closeQuestionModal() {
            questionModal.classList.add('hidden');
            currentEditingQuestion = null;
        }

        function handleQuestionTypeChange() {
            const type = document.getElementById('questionType').value;
            const optionsSection = document.getElementById('optionsSection');
            const likertSection = document.getElementById('likertSection');
            
            // Show/hide sections based on question type
            if (type === 'multiple' || type === 'checkbox') {
                optionsSection.classList.remove('hidden');
            } else {
                optionsSection.classList.add('hidden');
            }
            
            if (type === 'likert') {
                likertSection.classList.remove('hidden');
            } else {
                likertSection.classList.add('hidden');
            }
        }

        function saveQuestion(e) {
            e.preventDefault();
            
            const questionData = {
                id: currentEditingQuestion || Date.now(),
                type: document.getElementById('questionType').value,
                text: document.getElementById('questionText').value,
                help: document.getElementById('questionHelp').value,
                required: document.querySelector('input[name="required"]:checked').value === 'true',
                title: document.getElementById('questionText').value.substring(0, 30) + '...'
            };
            
            if (currentEditingQuestion) {
                const index = surveyQuestions.findIndex(q => q.id === currentEditingQuestion);
                surveyQuestions[index] = questionData;
            } else {
                surveyQuestions.push(questionData);
            }
            
            renderQuestions();
            renderPreview();
            closeQuestionModal();
        }

        function editQuestion(questionId) {
            openQuestionModal(questionId);
        }

        function duplicateQuestion(questionId) {
            const question = surveyQuestions.find(q => q.id === questionId);
            if (question) {
                const duplicated = { ...question, id: Date.now() };
                const index = surveyQuestions.findIndex(q => q.id === questionId);
                surveyQuestions.splice(index + 1, 0, duplicated);
                renderQuestions();
                renderPreview();
            }
        }

        function deleteQuestion(questionId) {
            if (confirm('Are you sure you want to delete this question?')) {
                surveyQuestions = surveyQuestions.filter(q => q.id !== questionId);
                renderQuestions();
                renderPreview();
            }
        }

        // Template functions
        function loadTemplate() {
            const template = document.getElementById('surveyTemplate').value;
            
            if (template === 'standard') {
                surveyQuestions = [...defaultTemplate];
            } else if (template === 'blank') {
                surveyQuestions = [];
            }
            // Add more template options here
            
            renderQuestions();
            renderPreview();
        }

        function openTemplateModal() {
            templateModal.classList.remove('hidden');
        }

        function closeTemplateModal() {
            templateModal.classList.add('hidden');
        }

        function saveTemplate(e) {
            e.preventDefault();
            
            const templateData = {
                name: document.getElementById('templateName').value,
                description: document.getElementById('templateDescription').value,
                category: document.getElementById('templateCategory').value,
                questions: surveyQuestions
            };
            
            // Here you would save to your backend
            console.log('Saving template:', templateData);
            alert('Template saved successfully!');
            closeTemplateModal();
        }

        // Survey actions
        function previewSurvey() {
            // Open survey in new window/tab for full preview
            window.open('survey-sample.php', '_blank');
        }

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

        
        