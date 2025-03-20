<?php
require_once '../includes/functions.php';

// Require admin privileges
requireAdmin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $questions = $_POST['questions'] ?? [];

    // Validate input
    if (empty($title)) {
        $errors[] = "Survey title is required";
    }

    if (empty($description)) {
        $errors[] = "Survey description is required";
    }

    if (empty($questions['text']) || !is_array($questions['text'])) {
        $errors[] = "At least one question is required";
    }

    // If no errors, create the survey
    if (empty($errors)) {
        $surveyId = createSurvey($title, $description);

        if ($surveyId) {
            // Add questions
            foreach ($questions['text'] as $index => $questionText) {
                if (!empty($questionText)) {
                    $type = $questions['type'][$index];
                    $options = !empty($questions['options'][$index]) ? 
                              explode("\n", trim($questions['options'][$index])) : 
                              null;
                    $required = isset($questions['required'][$index]);

                    addQuestion($surveyId, $questionText, $type, $options, $required, $index);
                }
            }

            $_SESSION['success'] = "Survey created successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Failed to create survey. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<div class="bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:text-4xl sm:truncate">
                    Create New Survey
                </h2>
                <p class="mt-1 text-lg text-gray-500">
                    Create a new survey with custom questions
                </p>
            </div>
            <div class="mt-5 flex lg:mt-0 lg:ml-4">
                <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left -ml-1 mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-8">
            <!-- Survey Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Survey Details</h3>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" required
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3" required
                                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Questions Section -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Questions</h3>
                    <button type="button" onclick="addQuestion()" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Add Question
                    </button>
                </div>

                <div id="questions-container" class="space-y-6">
                    <!-- Questions will be added here dynamically -->
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    Create Survey
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let questionCount = 0;

function addQuestion() {
    const container = document.getElementById('questions-container');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'bg-gray-50 rounded-lg p-4 question-item';
    questionDiv.innerHTML = `
        <div class="flex justify-between items-start mb-4">
            <h4 class="text-md font-medium text-gray-900">Question ${questionCount + 1}</h4>
            <button type="button" onclick="removeQuestion(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Question Text</label>
                <input type="text" name="questions[text][]" required
                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Question Type</label>
                <select name="questions[type][]" onchange="toggleOptions(this)" required
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="text">Text Input</option>
                    <option value="radio">Single Choice (Radio)</option>
                    <option value="checkbox">Multiple Choice (Checkbox)</option>
                </select>
            </div>
            <div class="options-container hidden">
                <label class="block text-sm font-medium text-gray-700">Options (one per line)</label>
                <textarea name="questions[options][]" rows="3"
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="questions[required][]" checked
                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Required question</span>
                </label>
            </div>
        </div>
    `;
    container.appendChild(questionDiv);
    questionCount++;
    updateQuestionNumbers();
}

function removeQuestion(button) {
    const questionDiv = button.closest('.question-item');
    questionDiv.remove();
    questionCount--;
    updateQuestionNumbers();
}

function updateQuestionNumbers() {
    const questions = document.querySelectorAll('.question-item h4');
    questions.forEach((question, index) => {
        question.textContent = `Question ${index + 1}`;
    });
}

function toggleOptions(select) {
    const optionsContainer = select.closest('.grid').querySelector('.options-container');
    const optionsTextarea = optionsContainer.querySelector('textarea');
    
    if (select.value === 'radio' || select.value === 'checkbox') {
        optionsContainer.classList.remove('hidden');
        optionsTextarea.required = true;
    } else {
        optionsContainer.classList.add('hidden');
        optionsTextarea.required = false;
    }
}

// Add first question by default
document.addEventListener('DOMContentLoaded', function() {
    addQuestion();
});
</script>

<?php include '../includes/footer.php'; ?>