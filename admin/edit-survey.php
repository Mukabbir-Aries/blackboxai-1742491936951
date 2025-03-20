<?php
require_once '../includes/functions.php';

// Require admin privileges
requireAdmin();

$errors = [];
$success = false;

// Get survey ID from URL
$surveyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$surveyId) {
    header("Location: dashboard.php");
    exit();
}

// Get survey details
$survey = getSurveyById($surveyId);

if (!$survey) {
    $_SESSION['error'] = "Survey not found.";
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? $survey['status'];
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

    // If no errors, update the survey
    if (empty($errors)) {
        // Update survey details
        $stmt = $conn->prepare("UPDATE surveys SET title = ?, description = ?, status = ? WHERE survey_id = ? AND created_by = ?");
        $stmt->bind_param("sssii", $title, $description, $status, $surveyId, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Delete existing questions
            $conn->query("DELETE FROM questions WHERE survey_id = $surveyId");
            
            // Add updated questions
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

            $_SESSION['success'] = "Survey updated successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Failed to update survey. Please try again.";
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
                    Edit Survey
                </h2>
                <p class="mt-1 text-lg text-gray-500">
                    Modify your existing survey
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
                               value="<?php echo htmlspecialchars($survey['title']); ?>">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3" required
                                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($survey['description']); ?></textarea>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" 
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="draft" <?php echo $survey['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $survey['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="closed" <?php echo $survey['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
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
                    <!-- Existing questions will be loaded here -->
                    <?php foreach ($survey['questions'] as $index => $question): ?>
                    <div class="bg-gray-50 rounded-lg p-4 question-item">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="text-md font-medium text-gray-900">Question <?php echo $index + 1; ?></h4>
                            <button type="button" onclick="removeQuestion(this)" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Question Text</label>
                                <input type="text" name="questions[text][]" required
                                       value="<?php echo htmlspecialchars($question['question_text']); ?>"
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Question Type</label>
                                <select name="questions[type][]" onchange="toggleOptions(this)" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="text" <?php echo $question['question_type'] === 'text' ? 'selected' : ''; ?>>Text Input</option>
                                    <option value="radio" <?php echo $question['question_type'] === 'radio' ? 'selected' : ''; ?>>Single Choice (Radio)</option>
                                    <option value="checkbox" <?php echo $question['question_type'] === 'checkbox' ? 'selected' : ''; ?>>Multiple Choice (Checkbox)</option>
                                </select>
                            </div>
                            <div class="options-container <?php echo $question['question_type'] === 'text' ? 'hidden' : ''; ?>">
                                <label class="block text-sm font-medium text-gray-700">Options (one per line)</label>
                                <textarea name="questions[options][]" rows="3"
                                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><?php 
                                    if ($question['options']) {
                                        echo htmlspecialchars(implode("\n", $question['options']));
                                    }
                                ?></textarea>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="questions[required][]"
                                           <?php echo $question['required'] ? 'checked' : ''; ?>
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Required question</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" onclick="confirmDelete()" 
                        class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-trash mr-2"></i>
                    Delete Survey
                </button>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let questionCount = <?php echo count($survey['questions']); ?>;

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

function confirmDelete() {
    if (confirm('Are you sure you want to delete this survey? This action cannot be undone.')) {
        window.location.href = `delete-survey.php?id=<?php echo $surveyId; ?>`;
    }
}
</script>

<?php include '../includes/footer.php'; ?>