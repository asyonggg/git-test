<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JRU Pulse Survey</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jru-blue': '#1e3a8a',
                        'jru-orange': '#f59e0b',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">

    <div class="container mx-auto max-w-2xl p-4 md:p-8">
        <div class="text-center mb-8">
            <img src="assets/jru-pulse-logo.png" alt="JRU Pulse Logo" class="mx-auto h-16 w-auto">
        </div>

        <div id="surveyContainer" class="bg-white p-6 md:p-8 rounded-xl shadow-lg">
            <!-- The entire survey will be rendered here by JavaScript -->
            <h1 id="surveyTitle" class="text-2xl font-bold text-gray-900 mb-2">Loading Survey...</h1>
            <p id="surveyDescription" class="text-gray-600 mb-6"></p>
            
            <form id="surveyForm">
                <div id="questionsContainer" class="space-y-6">
                    <!-- Questions will be dynamically inserted here -->
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" class="w-full bg-jru-blue text-white py-3 px-6 rounded-lg text-lg font-semibold hover:bg-blue-800 transition-colors">
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/take-survey.js"></script>
</body>
</html>