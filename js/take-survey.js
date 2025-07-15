    // Holds all information for the user's entire session.
    const surveyState = {
        surveyId: null,
        respondent: { type: null, identifier: null, first_name: null, last_name: null, id: null },
        surveyData: null,
        answers: {},
        currentQuestionIndex: 0,
    };

    const surveyContainer = document.getElementById('surveyContainer'); // The main HTML container where we render all our content.
    let isGoogleReady = false;

    window.onGoogleScriptLoad = function() {
        console.log("Checkpoint 1: Google script loaded.");
        google.accounts.id.initialize({
            client_id: "913799866499-p05hvm7muoaiqogtp85d0s95jiuavfuv.apps.googleusercontent.com",
            callback: handleGoogleSignIn
        });
        google.accounts.id.renderButton(
            document.getElementById("googleSignInButton"),
            { theme: "outline", size: "large", width: "380", text: "signin_with" }
        );
    };

    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        surveyState.surveyId = urlParams.get('id');
        if (!surveyState.surveyId) {
            renderError("No survey ID was provided.");
            return;
        }
        renderConsentStage();
    });

    function renderConsentStage() {
        surveyContainer.innerHTML = `
            <h1 class="text-2xl font-bold text-gray-900 mb-4 text-center">Data Privacy Notice</h1>
            <p class="text-gray-600 mb-6 text-center">
                Your feedback is vital for improving our services. By proceeding, you consent to the collection and processing of your responses by José Rizal University for this purpose.
            </p>
            <div class="mt-6 flex justify-center">
                <button id="agreeBtn" class="bg-jru-blue text-white py-3 px-8 rounded-lg font-semibold hover:bg-blue-800">
                    I Agree & Continue
                </button>
            </div>
        `;
        document.getElementById('agreeBtn').onclick = renderRoleSelectionStage;
    }
            

    function renderRoleSelectionStage() {
        console.log("Checkpoint 2: Rendering role selection screen.");
        surveyContainer.innerHTML = `
            <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">How are you connected to JRU?</h1>
            <div class="space-y-4">
                <div id="googleSignInButton"></div>
                <button id="visitorBtn" class="w-full bg-gray-600 text-white py-4 px-6 rounded-lg text-lg font-semibold hover:bg-gray-700">I am a Parent, Alumni, or Visitor</button>
            </div>
        `;

        document.getElementById('visitorBtn').onclick = handleVisitorPath;

        // We will wait a very short moment to give the Google script a chance to load, then we will try to render the button.
        setTimeout(() => {
            try {
                if (typeof google === 'undefined' || !google.accounts) { // Check if the 'google' object is available.
                    throw new Error("Google library not yet loaded."); // If not, throw an error to be caught below.
                }

                console.log("Attempting to initialize and render Google button...");
                
                google.accounts.id.initialize({ // Initialize the library.
                    client_id: "913799866499-p05hvm7muoaiqogtp85d0s95jiuavfuv.apps.googleusercontent.com",
                    callback: handleGoogleSignIn // Use the new, correct handler name
                });

                google.accounts.id.renderButton( // Immediately render the button in the div we just created.
                    document.getElementById("googleSignInButton"),
                    { theme: "outline", size: "large", width: "600", text: "signin_with" }
                );
                console.log("Google button rendered successfully.");

            } catch (error) {
                console.error("Could not render Google button:", error);
                const googleButtonDiv = document.getElementById("googleSignInButton"); // Display a helpful error message to the user inside the button's div.
                if(googleButtonDiv) {
                googleButtonDiv.innerHTML = "<p class='text-center text-red-500 p-3 bg-red-50 rounded-lg'>Could not load Google Sign-In. Please check your internet connection and refresh the page.</p>";
                }
            }
        }, 500); // Wait .5 sec before trying
    }

    function renderGoogleButton() {
        const googleButtonDiv = document.getElementById("googleSignInButton");
        
        if (googleButtonDiv) { // A safety check: if we are not on the role selection screen, the div won't exist.
            console.log("  > Div found. Rendering button now!"); //for debugging
            google.accounts.id.renderButton(
                googleButtonDiv,
                { theme: "outline", size: "large", width: "600", text: "signin_with" }
            );
        } else {
            console.log("  > Div not found yet. Button will be rendered when the user gets to that screen.");
        }
    }

    async function handleGoogleSignIn(googleResponse) {
        const userInfo = parseJWT(googleResponse.credential);
        if (!userInfo) {
            renderError("Could not verify your identity. Please try again.");
            return;
        }

        if (!userInfo.email.endsWith('@my.jru.edu') && !userInfo.email.endsWith('@jru.edu.ph')) {
            renderError("Sign-in failed. Please use a valid JRU email account.");
            return;
        }
        const dataToSend = {
            type: 'student',
            identifier: userInfo.email,
            first_name: userInfo.given_name, // Get first name from Google
            last_name: userInfo.family_name   // Get last name from Google
        };
        registerAndProceed(dataToSend);
    }

    function handleVisitorPath() { //function for non-students
        surveyContainer.innerHTML = `
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Please Provide Your Information</h1>
            <p class="text-sm text-gray-600 mb-4">Your name and email are required to proceed.</p>
            <form id="visitorForm">
                <div class="space-y-4">
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" id="firstName" name="first_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div>
                        <label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" id="lastName" name="last_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" name="identifier" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>
                <button type="submit" class="mt-6 w-full bg-jru-blue text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-800">Start Survey</button>
            </form>
        `;
        document.getElementById('visitorForm').onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const dataToSend = {
                type: 'non-student',
                identifier: formData.get('identifier'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name')
            };
            registerAndProceed(dataToSend);
        };
    }

    async function registerAndProceed(dataToSend) {
        renderLoading("Registering your session...");
        try {
            const response = await fetch('api/register-respondent.php', { //API endpoint name for VIsitor
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataToSend)
            });
            const result = await response.json();
            if (result.success) {
                surveyState.respondent = dataToSend;
                surveyState.respondent.id = result.data.respondent_id;
                // Show a brief, personalized welcome message
                surveyContainer.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                        <h1 class="text-2xl font-bold text-green-600">Verification Successful!</h1>
                        <p class="text-gray-600 mt-2">Welcome, ${dataToSend.first_name}!</p>
                    </div>
                `;
                setTimeout(() => fetchAndPrepareSurvey(), 2000); // Wait 2s before starting the survey
                
            } else {
                renderError(result.message);
            }
        } catch (error) {
            renderError("An error occurred while registering your session.");
        }
    }

    function parseJWT(token) {  // --- Helper Functions (including custom JWT parser) ---
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
            return JSON.parse(jsonPayload);
        } catch (e) { return null; }
    }

     function renderError(message) {
        surveyContainer.innerHTML = `<div class="text-center py-8 bg-red-50 p-6 rounded-lg"><i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i><h1 class="text-2xl font-bold text-red-600">An Error Occurred</h1><p class="text-gray-700 mt-2">${message}</p></div>`;
    }

    function renderLoading(message) {
        surveyContainer.innerHTML = `
            <div class="text-center py-8">
                <h1 class="text-2xl font-bold text-gray-900">${message}</h1>
                <!-- Optional: You could add a spinning icon here for a better visual effect -->
                <i class="fas fa-spinner fa-spin text-jru-blue text-4xl mt-4"></i>
            </div>
        `;
    }


    async function fetchAndPrepareSurvey() {
        const id = surveyState.surveyId; // Get the ID from our global state object. This is our single source of truth.

        if (!id) { // A safety check to make sure the ID actually exists before we make an API call.
            renderError("Cannot load survey because the ID is missing.");
            return;
        }

        console.log("About to fetch survey. Using ID from state:", id);
        renderLoading("Loading Survey...");

        try {
            const response = await fetch(`api/surveys.php?id=${id}`);  // Use the local 'id' variable in the fetch call.
            const result = await response.json();

            if (result.success && result.data.questions_json) {
                surveyState.surveyData = result.data;
                surveyState.surveyData.questions = JSON.parse(result.data.questions_json);

                if (Array.isArray(surveyState.surveyData.questions) && surveyState.surveyData.questions.length > 0) {
                    surveyState.currentQuestionIndex = 0;
                    renderQuestionStage();
                } else {
                    renderError("This survey has no questions yet.");
                }
            } else {
                renderError(result.message || "Survey could not be loaded or is empty.");
            }
        } catch (error) {
            console.error("Error fetching survey:", error);
            renderError("Could not load the survey. It may not exist or there was a network error.");
        }
    }

    function renderQuestionStage() {
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
                <label class="block text-lg font-semibold text-gray-800 mb-2">${currentQuestion.text} ${currentQuestion.required ? '<span class="text-red-500 ml-1">*</span>' : ''}</label>
                <p class="text-sm text-gray-500 mb-4">${currentQuestion.help || ''}</p>
                ${renderInputForQuestion(currentQuestion)}
            </div>
            <div id="warning-spot" class="mt-4"></div>
            <div class="mt-8 flex justify-between items-center">
                <button id="backBtn" class="${currentIndex === 0 ? 'invisible' : ''} bg-gray-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-gray-700">Back</button>
                <button id="nextBtn" class="bg-jru-blue text-white py-2 px-6 rounded-lg font-semibold hover:bg-blue-800">${currentIndex === questions.length - 1 ? 'Finish & Submit' : 'Next'}</button>
            </div>
        `;
        document.getElementById('backBtn').onclick = handleBack;
        document.getElementById('nextBtn').onclick = handleNext;
        setupQuestionInteractivity();
    }

        function handleBack() {
            if (surveyState.currentQuestionIndex > 0) {
                surveyState.currentQuestionIndex--;
                renderQuestionStage();
            }
        }

        function handleNext() {
            const currentQuestion = surveyState.surveyData.questions[surveyState.currentQuestionIndex];
            const inputName = `q_${currentQuestion.id}`;
            const inputWrapper = document.getElementById('question-input-wrapper');
            let inputValue = null;
            const inputElement = inputWrapper.querySelector(`[name="${inputName}"]`);
            if (inputElement) {
                if (inputElement.type === 'radio') {
                    const checkedRadio = inputWrapper.querySelector(`[name="${inputName}"]:checked`);
                    if (checkedRadio) inputValue = checkedRadio.value;
                } else {
                    inputValue = inputElement.value;
                }
            }
            const warningSpot = document.getElementById('warning-spot');
            if (currentQuestion.required && (!inputValue || inputValue.trim() === '')) {
                warningSpot.innerHTML = `<div class="text-red-600 font-semibold text-sm p-3 bg-red-50 rounded-lg"><i class="fas fa-exclamation-circle mr-2"></i>This question is required.</div>`;
                setTimeout(() => { warningSpot.innerHTML = ''; }, 3000);
                return;
            }
            warningSpot.innerHTML = '';
            surveyState.answers[inputName] = inputValue;
            if (surveyState.currentQuestionIndex < surveyState.surveyData.questions.length - 1) {
                surveyState.currentQuestionIndex++;
                renderQuestionStage();
            } else {
                submitSurveyResponse();
            }
        }

        async function submitSurveyResponse() {
            renderLoading("Submitting your feedback...");
            const finalAnswers = Object.entries(surveyState.answers).map(([key, value]) => {
                const qId = key.split('_')[1];
                const question = surveyState.surveyData.questions.find(q => q.id == qId);
                return { question_id: qId, text: question ? question.text : 'Unknown', answer: value };
            });
            const finalSubmissionData = { survey_id: surveyState.surveyId, respondent: surveyState.respondent, answers: finalAnswers };
            try {
                const response = await fetch('api/submit-response.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(finalSubmissionData)
                });
                const result = await response.json();
                if (result.success) {
                    surveyContainer.innerHTML = `<div class="text-center py-8">
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                    <h1 class="text-2xl font-bold text-gray-900">${result.message}</h1>
                    <p class="text-gray-600 mt-2">Thank you for helping us improve!</p></div>`;
                } else {
                    renderError(result.message);
                }
            } catch (error) {
                renderError("A network error occurred while submitting your feedback.");
            }
        }

   function renderInputForQuestion(question) { // --- UTILITY & HELPER FUNCTIONS ---
        const name = `q_${question.id}`;
        const savedAnswer = surveyState.answers[name] || '';

        let content = `<div id="question-input-wrapper">`;   // We will use a simple DIV as a wrapper for the inputs. give it a unique ID so we can easily find it later.

        switch (question.type) {
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
        content += '</div>'; // Close the DIV tag instead of the FORM tag.
        return content;
    }

     function setupQuestionInteractivity() {
        document.querySelectorAll('.emoji-label').forEach(label => {  // Emoji Hover Interactivity
            const textPopup = label.querySelector('.emoji-text-popup');
            
            label.addEventListener('mouseenter', () => {  // When the mouse enters/hover the label area
                label.style.transform = 'scale(1.15)'; // Enlarge the whole label
                if (textPopup) {
                    textPopup.style.opacity = '1'; // Make text visible
                }
            });

            label.addEventListener('mouseleave', () => {   // When the mouse leaves the label area
                label.style.transform = 'scale(1)'; // Return to normal size
                if (textPopup) {
                    textPopup.style.opacity = '0'; // Make text invisible
                }
            });
        });
        
        document.querySelectorAll('.emoji-label input[type="radio"]').forEach(radio => { // Re-check selected radio button for emojis
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