<?php
require_once '../includes/functions.php';

// Require admin privileges
requireAdmin();

// Get survey ID from URL
$surveyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$surveyId) {
    header("Location: dashboard.php");
    exit();
}

// Get survey details and responses
$survey = getSurveyById($surveyId);
$responses = getResponsesBySurvey($surveyId);

if (!$survey) {
    $_SESSION['error'] = "Survey not found.";
    header("Location: dashboard.php");
    exit();
}

include '../includes/header.php';

// Helper function to calculate response statistics
function calculateStats($responses, $questionId) {
    $stats = [
        'total' => 0,
        'options' => []
    ];
    
    foreach ($responses as $response) {
        if ($response['question_id'] == $questionId) {
            $stats['total']++;
            
            if ($response['question_type'] !== 'text') {
                // Handle multiple responses (comma-separated)
                $values = explode(", ", $response['response_text']);
                foreach ($values as $value) {
                    if (!isset($stats['options'][$value])) {
                        $stats['options'][$value] = 0;
                    }
                    $stats['options'][$value]++;
                }
            }
        }
    }
    
    return $stats;
}
?>

<div class="bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="lg:flex lg:items-center lg:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:text-4xl sm:truncate">
                    Survey Responses
                </h2>
                <p class="mt-1 text-lg text-gray-500">
                    Viewing responses for: <?php echo htmlspecialchars($survey['title']); ?>
                </p>
            </div>
            <div class="mt-5 flex lg:mt-0 lg:ml-4">
                <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left -ml-1 mr-2"></i>
                    Back to Dashboard
                </a>
                <a href="#" onclick="exportResponses()" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-download -ml-1 mr-2"></i>
                    Export Responses
                </a>
            </div>
        </div>

        <!-- Response Summary -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Response Summary
                </h3>
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Responses
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            <?php 
                            $uniqueResponders = array_unique(array_column($responses, 'user_id'));
                            echo count($uniqueResponders);
                            ?>
                        </dd>
                    </div>
                    <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Last Response
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            <?php 
                            if (!empty($responses)) {
                                echo date('M d, Y', strtotime($responses[0]['submitted_at']));
                            } else {
                                echo "N/A";
                            }
                            ?>
                        </dd>
                    </div>
                    <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Completion Rate
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            <?php 
                            if (!empty($responses)) {
                                $totalQuestions = count($survey['questions']);
                                $completedResponses = array_filter($uniqueResponders, function($userId) use ($responses, $totalQuestions) {
                                    $userResponses = array_filter($responses, function($r) use ($userId) {
                                        return $r['user_id'] == $userId;
                                    });
                                    return count($userResponses) == $totalQuestions;
                                });
                                echo round((count($completedResponses) / count($uniqueResponders)) * 100) . "%";
                            } else {
                                echo "0%";
                            }
                            ?>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Response Details -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Detailed Responses
                </h3>

                <?php if (!empty($responses)): ?>
                    <?php foreach ($survey['questions'] as $question): ?>
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h4 class="text-xl font-medium text-gray-900 mb-4">
                            <?php echo htmlspecialchars($question['question_text']); ?>
                        </h4>

                        <?php
                        $stats = calculateStats($responses, $question['question_id']);
                        
                        if ($question['question_type'] === 'text'):
                        ?>
                            <!-- Text responses -->
                            <div class="space-y-4">
                                <?php
                                foreach ($responses as $response):
                                    if ($response['question_id'] == $question['question_id']):
                                ?>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-gray-900"><?php echo htmlspecialchars($response['response_text']); ?></p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Submitted by <?php echo htmlspecialchars($response['username'] ?? 'Anonymous'); ?> on 
                                        <?php echo date('M d, Y H:i', strtotime($response['submitted_at'])); ?>
                                    </p>
                                </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        <?php else: ?>
                            <!-- Multiple choice responses -->
                            <div class="space-y-4">
                                <?php foreach ($stats['options'] as $option => $count): ?>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-gray-900"><?php echo htmlspecialchars($option); ?></span>
                                        <span class="text-gray-600">
                                            <?php 
                                            $percentage = ($count / $stats['total']) * 100;
                                            echo round($percentage) . "% (" . $count . " responses)";
                                            ?>
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-clipboard-list text-gray-400 text-5xl mb-4"></i>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No responses yet</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Wait for participants to submit their responses.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportResponses() {
    // Create CSV content
    let csv = 'Question,Response,User,Submitted At\n';
    
    <?php
    foreach ($responses as $response) {
        $questionText = addslashes($response['question_text']);
        $responseText = addslashes($response['response_text']);
        $username = addslashes($response['username'] ?? 'Anonymous');
        $submittedAt = $response['submitted_at'];
        
        echo "csv += '\"$questionText\",\"$responseText\",\"$username\",\"$submittedAt\"\n';";
    }
    ?>
    
    // Create and trigger download
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'survey_responses.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

<?php include '../includes/footer.php'; ?>