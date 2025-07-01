document.addEventListener('DOMContentLoaded', () => {
    // 1. Get the survey ID from the page's URL.
    const urlParams = new URLSearchParams(window.location.search);
    const surveyId = urlParams.get('id');

    // 2. Check if an ID exists.
    if (!surveyId) {
        renderError("No survey ID was provided. Please use a valid survey link.");
        return; // Stop if no ID.
    }
    
    // 3. Start the process by showing the very first screen.
    renderIdentificationStage(surveyId);
});

const surveyContainer = document.getElementById('surveyContainer');


// --- STAGE 1: Render the "Who are you?" choice ---
function renderIdentificationStage(surveyId) {
    surveyContainer.innerHTML = `
        <h1 class="text-2xl font-bold text-gray-900 mb-4 text-center">How are you connected to JRU?</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <button id="isStudentBtn" class="bg-jru-blue text-white py-4 px-6 rounded-lg text-lg font-semibold hover:bg-blue-800 transition-colors">
                I am a Student
            </button>
            <button id="isVisitorBtn" class="bg-gray-600 text-white py-4 px-6 rounded-lg text-lg font-semibold hover:bg-gray-700 transition-colors">
                Parent / Visitor / Other
            </button>
        </div>
    `;
    // Attach event listeners to the new buttons.
    document.getElementById('isStudentBtn').onclick = () => renderStudentForm(surveyId);
    document.getElementById('isVisitorBtn').onclick = () => renderVisitorForm(surveyId);
}


// --- STAGE 2 (Path A): Render the form for Students ---
function renderStudentForm(surveyId) {
    surveyContainer.innerHTML = `
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Student Information</h1>
        <form id="respondentForm">
            <div class="space-y-4">
                <div>
                    <label for="studentNumber" class="block text-sm font-medium text-gray-700">Student Number</label>
                    <input type="text" id="studentNumber" name="identifier" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" placeholder="e.g., 25-123456" required>
                </div>
                <div>
                    <label for="division" class="block text-sm font-medium text-gray-700">Division</label>
                    <select id="division" name="division" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Select Division...</option>
                        <option value="College">College</option>
                        <option value="SHS">Senior High School</option>
                        <option value="JHS">Junior High School</option>
                        <option value="Law School">Law School</option>
                        <option value="Graduate School">Graduate School</option>
                    </select>
                </div>
                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700">Course / Strand (e.g., BSIT, ABM)</label>
                    <input type="text" id="course" name="course" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                </div>
            </div>
            <button type="submit" class="mt-6 w-full bg-jru-blue text-white py-3 px-6 rounded-lg text-lg font-semibold hover:bg-blue-800">
                Proceed to Survey
            </button>
        </form>
    `;
    document.getElementById('respondentForm').onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const respondentData = Object.fromEntries(formData.entries());
        respondentData.type = 'student'; // Set the type
        fetchAndRenderSurvey(surveyId, respondentData); // Proceed to the next stage
    };
}


// --- STAGE 2 (Path B): Render the form for Visitors/Others ---
function renderVisitorForm(surveyId) {
    surveyContainer.innerHTML = `
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Welcome!</h1>
        <form id="respondentForm">
            <p class="text-gray-600 mb-4">Please provide an email address to proceed. This is optional and helps us track feedback.</p>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address (Optional)</label>
                <input type="email" id="email" name="identifier" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" placeholder="you@example.com">
            </div>
            <button type="submit" class="mt-6 w-full bg-jru-blue text-white py-3 px-6 rounded-lg text-lg font-semibold hover:bg-blue-800">
                Proceed to Survey
            </button>
        </form>
    `;
    document.getElementById('respondentForm').onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        let respondentData = Object.fromEntries(formData.entries());
        if (!respondentData.identifier) {
            respondentData.identifier = 'anon-' + Date.now() + Math.random();
        }
        respondentData.type = 'visitor'; // Set the type
        fetchAndRenderSurvey(surveyId, respondentData); // Proceed to the next stage
    };
}


// --- STAGE 3: Fetch the survey data from the API ---
async function fetchAndRenderSurvey(surveyId, respondentData) {
    surveyContainer.innerHTML = `<h1 class="text-2xl font-bold text-gray-900 mb-2">Loading Survey...</h1>`;
    try {
        const response = await fetch(`api/surveys.php?id=${surveyId}`);
        const result = await response.json();
        if (result.success) {
            renderSurveyQuestions(surveyId, result.data, respondentData);
        } else {
            renderError(result.message);
        }
    } catch (error) { 
        console.error("Error fetching survey:", error);
        renderError("Could not load the survey. It may not exist or there was a network error.");
    }
}


// --- STAGE 4: Render the actual survey questions ---
    function renderSurveyQuestions(surveyId, survey, respondentData) {
        surveyContainer.innerHTML = `
            <h1 class="text-2xl font-bold text-gray-900 mb-2">${survey.title}</h1>
            <p class="text-gray-600 mb-6">${survey.description || ''}</p>
            <form id="surveyForm"></form>
        `;
        const surveyForm = document.getElementById('surveyForm');
        const questionsContainer = document.createElement('div');
        questionsContainer.className = 'space-y-8'; // Increased space between questions
        
        if (survey.questions_json && Array.isArray(survey.questions_json.questions)) {
            survey.questions_json.questions.forEach((q, index) => {
                const questionEl = document.createElement('div');
                questionEl.className = 'py-4 border-b border-gray-200';
                
                // This is the input field HTML that will be generated
                const inputHtml = renderInputForQuestion(q);
                
                questionEl.innerHTML = `
                    <label class="block text-lg font-semibold text-gray-800 mb-2">
                        ${index + 1}. ${q.text} ${q.required ? '<span class="text-red-500 ml-1">*</span>' : ''}
                    </label>
                    <p class="text-sm text-gray-500 mb-4">${q.help || ''}</p>
                    ${inputHtml}
                `;
                questionsContainer.appendChild(questionEl);
            });
        }
        
        surveyForm.appendChild(questionsContainer);
        surveyForm.innerHTML += `
            <div class="mt-8 pt-6">
                <button type="submit" class="w-full bg-jru-blue text-white py-3 px-6 rounded-lg text-lg font-semibold">Submit Feedback</button>
            </div>
        `;

        surveyForm.onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const answers = [];
            // The logic to gather answers needs to be smarter now
            survey.questions_json.questions.forEach(q => {
                const key = `q_${q.id}`;
                let answer = formData.get(key);
                // For checkboxes, we get all selected values
                if (q.type === 'checkbox') {
                    answer = formData.getAll(key);
                }
                answers.push({ question_id: q.id, text: q.text, answer: answer });
            });
            
            const submissionData = { survey_id: surveyId, respondent: respondentData, answers: answers };
            submitSurveyResponse(submissionData);
        };
    }



    // --- STAGE 5: Submit the final response to the API ---
    async function submitSurveyResponse(submissionData) {
        surveyContainer.innerHTML = `<h1 class="text-2xl font-bold text-gray-900 mb-2">Submitting...</h1>`;
        try {
            const response = await fetch('api/submit-response.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(submissionData)
            });
            const result = await response.json();
            if (result.success) {
                surveyContainer.innerHTML = `<div class="text-center py-8"><i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i><h1 class="text-2xl font-bold text-gray-900">${result.message}</h1></div>`;
            } else {
                renderError(result.message);
            }
        } catch (error) { 
            console.error("Error submitting response:", error);
            renderError("A network error occurred while submitting your feedback.");
        }
    }                                                           



        // This function generates the correct HTML for each question type.
        function renderInputForQuestion(question) {
            const name = `q_${question.id}`;
            const required = question.required ? 'required' : '';

            switch (question.type) {
                case 'likert':
                    return `
                        <div class="flex flex-wrap justify-between items-center text-center text-sm text-gray-600">
                            <span>Poor</span>
                            <div class="flex space-x-2">
                                ${[1,2,3,4,5].map(i => `
                                    <label class="flex flex-col items-center">
                                        ${i}
                                        <input type="radio" name="${name}" value="${i}" class="mt-1" ${required}>
                                    </label>
                                `).join('')}
                            </div>
                            <span>Excellent</span>
                        </div>`;

                case 'text':
                    return `<input type="text" name="${name}" class="w-full p-2 border border-gray-300 rounded-lg" ${required}>`;

                case 'textarea':
                    return `<textarea name="${name}" rows="4" class="w-full p-2 border border-gray-300 rounded-lg" ${required}></textarea>`;
                
                case 'multiple': // Radio buttons
                    return (question.options || []).map(opt => `
                        <label class="flex items-center space-x-3 p-2 border rounded-lg mb-2">
                            <input type="radio" name="${name}" value="${opt.value}" class="h-4 w-4" ${required}>
                            <span>${opt.label}</span>
                        </label>
                    `).join('');

                case 'checkbox':
                    return (question.options || []).map(opt => `
                        <label class="flex items-center space-x-3 p-2 border rounded-lg mb-2">
                            <input type="checkbox" name="${name}" value="${opt.value}" class="h-4 w-4 rounded">
                            <span>${opt.label}</span>
                        </label>
                    `).join('');

                default:
                    return `<p class="text-red-500">Error: Unknown question type "${question.type}"</p>`;
            }
        }


// --- UTILITY: A helper function to render errors ---
function renderError(message) {
    surveyContainer.innerHTML = `<h1 class="text-2xl font-bold text-red-600">Error</h1><p class="text-gray-700">${message}</p>`;
}