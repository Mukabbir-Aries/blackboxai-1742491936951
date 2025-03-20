<?php
require_once 'includes/functions.php';

// Require login to take surveys
requireLogin();

$errors = [];
$success = false;
$survey = null;

// Get survey ID from URL
$surveyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$surveyId) {
    header("Location: surveys.php");
    exit();
}

// Get survey details
$survey = getSurveyById($surveyId);

if (!$survey) {
    $_SESSION['error'] = "Survey not found or not available.";
    header("Location: surveys.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $responses = $_POST['responses'] ?? [];
    
    // Validate responses
    foreach ($survey['questions'] as $question) {
        if ($question['required'] && empty($responses[$question['question_id']])) {
            $errors[] = "Question '" . htmlspecialchars($question['question_text']) . "' is required.";
        }
    }

    // If no errors, save responses
    if (empty($errors)) {
        $allSaved = true;
        foreach ($responses as $questionId => $response) {
            // Handle array responses (checkboxes)
            if (is_array($response)) {
                $response = implode(", ", $response);
            }
            
            if (!saveSurveyResponse($surveyId, $questionId, $response)) {
                $allSaved = false;
                break;
            }
        }

        if ($allSaved) {
            $_SESSION['success'] = "Thank you for completing the survey!";
            header("Location: surveys.php");
            exit();
        } else {
            $errors[] = "Failed to save some responses. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="bg-white py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Survey Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <?php echo htmlspecialchars($survey['title']); ?>
            </h1>
            <p class="mt-4 text-lg text-gray-500">
                <?php echo htmlspecialchars($survey['description']); ?>
            </p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Survey Form -->
        <form method="POST" action="" class="space-y-8">
            <?php foreach ($survey['questions'] as $index => $question): ?>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="mb-4">
                    <label class="block text-lg font-medium text-gray-900">
                        <?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?>
                        <?php if ($question['required']): ?>
                            <span class="text-red-500">*</span>
                        <?php endif; ?>
                    </label>
                </div>

                <div class="mt-4">
                    <?php 
                    $responseValue = isset($_POST['responses'][$question['question_id']]) 
                        ? $_POST['responses'][$question['question_id']] 
                        : '';
                    
                    switch ($question['question_type']):
                        case 'text':
                    ?>
                        <textarea 
                            name="responses[<?php echo $question['question_id']; ?>]" 
                            rows="3" 
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            <?php echo $question['required'] ? 'required' : ''; ?>
                        ><?php echo htmlspecialchars($responseValue); ?></textarea>
                    <?php
                        break;
                        case 'radio':
                        if (!empty($question['options'])):
                            foreach ($question['options'] as $option):
                    ?>
                        <div class="flex items-center mb-3">
                            <input 
                                type="radio" 
                                id="option_<?php echo $question['question_id'] . '_' . md5($option); ?>"
                                name="responses[<?php echo $question['question_id']; ?>]" 
                                value="<?php echo htmlspecialchars($option); ?>"
                                <?php echo $responseValue === $option ? 'checked' : ''; ?>
                                <?php echo $question['required'] ? 'required' : ''; ?>
                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                            >
                            <label 
                                for="option_<?php echo $question['question_id'] . '_' . md5($option); ?>"
                                class="ml-3 block text-sm font-medium text-gray-700"
                            >
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        </div>
                    <?php
                            endforeach;
                        endif;
                        break;
                        case 'checkbox':
                        if (!empty($question['options'])):
                            foreach ($question['options'] as $option):
                    ?>
                        <div class="flex items-center mb-3">
                            <input 
                                type="checkbox" 
                                id="option_<?php echo $question['question_id'] . '_' . md5($option); ?>"
                                name="responses[<?php echo $question['question_id']; ?>][]" 
                                value="<?php echo htmlspecialchars($option); ?>"
                                <?php echo is_array($responseValue) && in_array($option, $responseValue) ? 'checked' : ''; ?>
                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                            >
                            <label 
                                for="option_<?php echo $question['question_id'] . '_' . md5($option); ?>"
                                class="ml-3 block text-sm font-medium text-gray-700"
                            >
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        </div>
                    <?php
                            endforeach;
                        endif;
                        break;
                    endswitch;
                    ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="flex justify-between items-center pt-6">
                <a href="surveys.php" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Surveys
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Submit Survey
                    <i class="fas fa-paper-plane ml-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>