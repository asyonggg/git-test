    const surveyState = {
        surveyId: null,
        respondent: { //  will hold the user's info (e.g., email, type).
            type: null,
            identifier: null,
            // add more details here later, like their name from Google.
        },
        surveyData: null,
        answers: {},
        currentQuestionIndex: 0,
    };

    // --- The Starting Point of the Application ---
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);  // Get the survey ID from the page's URL (e.g., ?id=35).
        surveyState.surveyId = urlParams.get('id');

        if (!surveyState.surveyId) {
            renderError("No survey ID was provided. Please use a valid survey link.");
            return; // Stop everything if the link is broken.
        }
        renderConsentStage();  // Begin the user's journey at the very first step.
    });

    const surveyContainer = document.getElementById('surveyContainer'); //main container in HTML where all content will be rendered

    function initializeSurvey() {

        const urlParams = new URLSearchParams(window.location.search); // Get the survey ID from the page's URL.
        surveyState.surveyId = urlParams.get('id');

        if (!surveyState.surveyId) {
            renderError("No survey ID was provided. Please use a valid survey link.");
            return; // Stop if no ID.
        }

        renderConsentStage(); // Consent form before taking the survey
    }

    function renderConsentStage() { // --- STAGE 0: Data Privacy Consent ---
        surveyContainer.innerHTML = `
            <h1 class="text-2xl font-bold text-gray-900 mb-4 text-center">Data Privacy Notice</h1>
            <p class="text-gray-600 mb-6 text-center">
                Your feedback is vital for improving our services. By proceeding, you consent to the collection and processing of your responses by José Rizal University for this purpose.
            </p>
            <div class="mt-6 flex justify-center">
                <button id="agreeBtn" class="bg-jru-blue text-white py-3 px-8 rounded-lg font-semibold hover:bg-blue-800 transition-transform hover:scale-105">
                    I Agree & Continue
                </button>
            </div>
        `;
        
        document.getElementById('agreeBtn').onclick = renderRoleSelectionStage; // When the user clicks "Agree", move them to the next stage.
    }

    function renderRoleSelectionStage() {  // --- STAGE 1: Role Selection ---
        surveyContainer.innerHTML = `
            <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">How are you connected to JRU?</h1>
            <div class="space-y-4">
                <button id="studentBtn" class="w-full bg-jru-blue text-white py-4 px-6 rounded-lg text-lg font-semibold hover:bg-blue-800 flex items-center justify-center space-x-3">
                    <i class="fab fa-google"></i>
                    <span>Sign in as JRU Student</span>
                </button>
                <button id="visitorBtn" class="w-full bg-gray-600 text-white py-4 px-6 rounded-lg text-lg font-semibold hover:bg-gray-700">
                    I am a Parent, Alumni, or Visitor
                </button>
            </div>
        `;

        document.getElementById('studentBtn').onclick = handleStudentPath;  // Attach click handlers to the new buttons.
        document.getElementById('visitorBtn').onclick = handleVisitorPath;
    }

       
    function handleStudentPath() {  // --- STAGE 2 (Path A): The Student's Path ---
        // TODO: This is where we will add the Google Sign-In logic in a future step.
        
        console.log("Student path selected. Google Sign-In will be implemented here."); // For now, will just show a placeholder message and simulate a successful login.
        
        const fakeGoogleData = { // Simulate getting data from Google.
            type: 'student',
            identifier: 'verified.student@my.jru.edu', // A fake verified email.
            name: 'Verified Rizalian'
        };
         
        surveyState.respondent = fakeGoogleData; // Save this "verified" data to our state.

        // TODO: In the future, we will call an API here to verify this email against the database
        // and create a respondent record. For now, we go directly to the survey.
        fetchAndPrepareSurvey();
    }

    function handleVisitorPath() {  // --- STAGE 2 (Path B): The Non-students Path ---
        console.log("Visitor path selected. Rendering manual form.");

        // Render the simple form for non-students.
        surveyContainer.innerHTML = `
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Please Provide Your Information</h1>
            <form id="visitorForm">
                <div class="space-y-4">
                    <div>
                        <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="fullName" name="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" name="identifier" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                    </div>
                </div>
                <button type="submit" class="mt-6 w-full bg-jru-blue text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-800">
                    Start Survey
                </button>
            </form>
        `;

        document.getElementById('visitorForm').onsubmit = (e) => { // Handle the form submission.
            e.preventDefault();
            const formData = new FormData(e.target);
            
            surveyState.respondent.type = 'non-student'; // Save the non-student data to our state.
            surveyState.respondent.identifier = formData.get('identifier');
            surveyState.respondent.name = formData.get('name');
            
            // TODO: In the future, this will call an API to create a respondent record.
            // For now, we go directly to the survey.
            fetchAndPrepareSurvey();
        };
    }

    function renderLoading(message) {
        surveyContainer.innerHTML = `<div class="text-center py-8"><h1 class="text-2xl font-bold text-gray-900">${message}</h1></div>`;
    }

    // Helper function to show an error message.
    function renderLoading(message) {
        surveyContainer.innerHTML = `<div class="text-center py-8"><h1 class="text-2xl font-bold text-gray-900">${message}</h1></div>`;
    }

    // --- The Survey Wizard Logic (PLACEHOLDER - We will add this back) ---
    async function fetchAndPrepareSurvey() {
        renderLoading("Loading Survey...");
        // For now, we'll stop here to test the new intro flow.
        setTimeout(() => {
            surveyContainer.innerHTML = `<div class="text-center py-8"><h1 class="text-2xl font-bold text-green-600">Flow Test Successful!</h1><p class="text-gray-700 mt-2">The next step is to load the questions. We are now ready to build that part.</p><pre class="text-left bg-gray-100 p-4 rounded-lg mt-4 text-sm">${JSON.stringify(surveyState.respondent, null, 2)}</pre></div>`;
        }, 1000);
    }


    function renderQuestionStage() { // --- STAGE 3: Render the Current Question (One-at-a-time) ---
        const questions = surveyState.surveyData.questions;
        const currentIndex = surveyState.currentQuestionIndex;
        const currentQuestion = questions[currentIndex];

        surveyContainer.innerHTML = `
            <div class="mb-4">
                <p class="text-sm font-bold text-jru-blue">Question ${currentIndex + 1} of ${questions.length}</p>
                <div class="w-full bg-gray-200 rounded-full mt-1">
                    <div class="bg-jru-blue h-2 rounded-full" style="width: ${((currentIndex + 1) / questions.length) * 100}%"></div>
                </div>
            </div>

            <div class="py-4">
                <label class="block text-lg font-semibold text-gray-800 mb-2">
                    ${currentQuestion.text} ${currentQuestion.required ? '<span class="text-red-500 ml-1">*</span>' : ''}
                </label>
                <p class="text-sm text-gray-500 mb-4">${currentQuestion.help || ''}</p>
                ${renderInputForQuestion(currentQuestion)}
            </div>

            <div class="mt-8 flex justify-between items-center">
                <button id="backBtn" class="${currentIndex === 0 ? 'invisible' : ''} bg-gray-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-gray-700">Back</button>
                <button id="nextBtn" class="bg-jru-blue text-white py-2 px-6 rounded-lg font-semibold hover:bg-blue-800">
                    ${currentIndex === questions.length - 1 ? 'Finish & Submit' : 'Next'}
                </button>
            </div>
        `;

        // Add event listeners for the new buttons and inputs
        document.getElementById('backBtn').onclick = handleBack;
        document.getElementById('nextBtn').onclick = handleNext;
        
        // Make sure star ratings and emojis are interactive
        setupQuestionInteractivity();
    }

    function handleBack() {
        // Check if we are not on the first question.
        if (surveyState.currentQuestionIndex > 0) {
            // Decrease the index to go to the previous question.
            surveyState.currentQuestionIndex--;
            // Re-render the question stage with the new index.
            renderQuestionStage();
        }
    }

    function handleNext() {
        const questions = surveyState.surveyData.questions;
        const currentQuestion = questions[surveyState.currentQuestionIndex];
        const inputName = `q_${currentQuestion.id}`;
        
        // Find the wrapper div we created.
        const inputWrapper = document.getElementById('question-input-wrapper');
        let inputValue = null;

        // --- The New, Simpler Way to Get the Value ---
        // Find the actual input element within our wrapper.
        const inputElement = inputWrapper.querySelector(`[name="${inputName}"]`);
        
        if (inputElement) {
            // For radio buttons (like our emojis), we need to find the one that is checked.
            if (inputElement.type === 'radio') {
                const checkedRadio = inputWrapper.querySelector(`[name="${inputName}"]:checked`);
                if (checkedRadio) {
                    inputValue = checkedRadio.value;
                }
            } else {
                // For all other types (textarea, hidden input for stars), we can just get the value directly.
                inputValue = inputElement.value;
            }
        }
        
        // The rest of the function is the same.
        if (currentQuestion.required && (!inputValue || inputValue.trim() === '')) {
            alert("This question is required. Please provide an answer to continue.");
            return;
        }
        
        surveyState.answers[inputName] = inputValue;

        if (surveyState.currentQuestionIndex < questions.length - 1) {
            surveyState.currentQuestionIndex++;
            renderQuestionStage();
        } else {
            submitSurveyResponse();
        }
    }


    async function submitSurveyResponse() {  // --- STAGE 4: Final Submission ---
        renderLoading("Submitting your feedback...");
        
        const finalAnswers = Object.entries(surveyState.answers).map(([key, value]) => {
            const qId = key.split('_')[1];
            const question = surveyState.surveyData.questions.find(q => q.id == qId);
            return {
                question_id: qId,
                text: question.text,
                answer: value,
            };
        });

        const submissionData = {
            survey_id: surveyState.surveyId,
            respondent: surveyState.respondentData,
            answers: finalAnswers,
        };
        
        try {
            const response = await fetch('api/submit-response.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(submissionData),
            });
            const result = await response.json();
            if (result.success) {
                surveyContainer.innerHTML = `<div class="text-center py-8"><i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i><h1 class="text-2xl font-bold text-gray-900">${result.message}</h1><p class="text-gray-600 mt-2">Thank you for helping us improve!</p></div>`;
            } else {
                renderError(result.message);
            }
        } catch (error) {
            console.error("Error submitting response:", error);
            renderError("A network error occurred while submitting your feedback.");
        }
    }

    
    function renderInputForQuestion(question) { // --- UTILITY & HELPER FUNCTIONS ---
        const name = `q_${question.id}`;
        const savedAnswer = surveyState.answers[name] || '';

        // We will use a simple DIV as a wrapper for the inputs.
        // We give it a unique ID so we can easily find it later.
        let content = `<div id="question-input-wrapper">`; 

        switch (question.type) {
            // ... (The 'case' blocks for likert, rating, textarea remain THE SAME) ...
        case 'likert': // Emoji Scale
        const emojis = [
            { emoji: '😍', value: 5, label: 'Excellent' },
            { emoji: '😊', value: 4, label: 'Very Good' },
            { emoji: '😐', value: 3, label: 'Good' },
            { emoji: '😞', value: 2, label: 'Fair' },
            { emoji: '😠', value: 1, label: 'Poor' }
        ];

        content += `
            <div class="flex justify-center space-x-2 md:space-x-4">
                ${emojis.map(item => `
                    <label class="emoji-label relative flex flex-col items-center cursor-pointer text-center p-2 transition-transform duration-200 ease-in-out">
                        
                        <!-- The Emoji -->
                        <span class="text-4xl md:text-5xl">${item.emoji}</span>
                        
                        <!-- The Text Label (starts hidden) -->
                        <span class="emoji-text-popup mt-2 text-xs text-gray-600 font-semibold opacity-0 transition-opacity">
                            ${item.label}
                        </span>
                        
                        <!-- The Radio Button (hidden) -->
                        <input type="radio" name="${name}" value="${item.value}" class="sr-only peer" ${savedAnswer == item.value ? 'checked' : ''}>
                        
                        <!-- The Checkmark Indicator -->
                        <div class="w-4 h-4 rounded-full border-2 border-gray-300 mt-2 peer-checked:bg-jru-blue peer-checked:border-jru-blue"></div>
                    </label>
                `).join('')}
            </div>`;
        break;
            
            case 'rating':
                content += `<div class="flex justify-center items-center text-4xl text-gray-300 star-rating" data-selected-value="${savedAnswer}">
                    ${[5,4,3,2,1].map(i => `<i class="${i <= savedAnswer ? 'fas text-yellow-400' : 'far'} fa-star cursor-pointer p-1" data-value="${i}"></i>`).join('')}
                </div>
                <input type="hidden" name="${name}" value="${savedAnswer}">`;
                break;

            case 'textarea':
                content += `<textarea name="${name}" rows="4" class="w-full p-3 border border-gray-300 rounded-lg">${savedAnswer}</textarea>`;
                break;
            
            default:
                content += `<p class="text-red-500 italic">This question type is not supported.</p>`;
        }
        
        // Close the DIV tag instead of the FORM tag.
        content += '</div>';
        return content;
    }

    function setupQuestionInteractivity() {
        // Emoji Hover Interactivity
        document.querySelectorAll('.emoji-label').forEach(label => {
            const textPopup = label.querySelector('.emoji-text-popup');
            
            // When the mouse enters the label area
            label.addEventListener('mouseenter', () => {
                label.style.transform = 'scale(1.15)'; // Enlarge the whole label
                if (textPopup) {
                    textPopup.style.opacity = '1'; // Make text visible
                }
            });

            // When the mouse leaves the label area
            label.addEventListener('mouseleave', () => {
                label.style.transform = 'scale(1)'; // Return to normal size
                if (textPopup) {
                    textPopup.style.opacity = '0'; // Make text invisible
                }
            });
        });
        
        // Re-check selected radio button for emojis
        document.querySelectorAll('.emoji-label input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', () => {
                // Un-style all siblings
                radio.closest('.flex').querySelectorAll('.emoji-label .w-4').forEach(div => div.classList.remove('bg-jru-blue', 'border-jru-blue'));
                // Style the selected one
                if(radio.checked) {
                    radio.nextElementSibling.classList.add('bg-jru-blue', 'border-jru-blue');
                }
            });
        });
    }

    function renderLoading(message) {
        surveyContainer.innerHTML = `<div class="text-center py-8"><h1 class="text-2xl font-bold text-gray-900">${message}</h1></div>`;
    }

    function renderError(message) {
        surveyContainer.innerHTML = `<div class="text-center py-8 bg-red-50 p-6 rounded-lg"><i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i><h1 class="text-2xl font-bold text-red-600">An Error Occurred</h1><p class="text-gray-700 mt-2">${message}</p></div>`;
    }