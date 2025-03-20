<?php
require_once '../includes/functions.php';

// Require admin privileges
requireAdmin();

// Get all surveys including drafts
$surveys = getSurveys(true);

include '../includes/header.php';
?>

<div class="bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Dashboard Header -->
        <div class="lg:flex lg:items-center lg:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:text-4xl sm:truncate">
                    Admin Dashboard
                </h2>
                <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <i class="fas fa-user-shield flex-shrink-0 mr-1.5"></i>
                        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </div>
                </div>
            </div>
            <div class="mt-5 flex lg:mt-0 lg:ml-4">
                <a href="create-survey.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus -ml-1 mr-2"></i>
                    Create New Survey
                </a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="mt-8 grid gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Total Surveys -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-poll text-blue-600 text-3xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Surveys
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        <?php echo count($surveys); ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Surveys -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Active Surveys
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        <?php 
                                        echo count(array_filter($surveys, function($survey) {
                                            return $survey['status'] === 'published';
                                        }));
                                        ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Draft Surveys -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-edit text-yellow-600 text-3xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Draft Surveys
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">
                                        <?php 
                                        echo count(array_filter($surveys, function($survey) {
                                            return $survey['status'] === 'draft';
                                        }));
                                        ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Survey Management -->
        <div class="mt-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h3 class="text-xl font-semibold text-gray-900">Survey Management</h3>
                    <p class="mt-2 text-sm text-gray-700">
                        A list of all surveys including drafts and published ones.
                    </p>
                </div>
            </div>

            <?php if (!empty($surveys)): ?>
            <div class="mt-8 flex flex-col">
                <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Title</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Responses</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <?php foreach ($surveys as $survey): ?>
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($survey['title']); ?></div>
                                            <div class="text-gray-500"><?php echo htmlspecialchars(substr($survey['description'], 0, 100)) . '...'; ?></div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <?php if ($survey['status'] === 'published'): ?>
                                            <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">Published</span>
                                            <?php else: ?>
                                            <span class="inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($survey['created_at'])); ?>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <a href="responses.php?id=<?php echo $survey['survey_id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                View Responses
                                            </a>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <a href="edit-survey.php?id=<?php echo $survey['survey_id']; ?>" class="text-blue-600 hover:text-blue-900 mr-4">
                                                Edit<span class="sr-only">, <?php echo htmlspecialchars($survey['title']); ?></span>
                                            </a>
                                            <a href="#" onclick="deleteSurvey(<?php echo $survey['survey_id']; ?>)" class="text-red-600 hover:text-red-900">
                                                Delete<span class="sr-only">, <?php echo htmlspecialchars($survey['title']); ?></span>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center py-12 bg-gray-50 rounded-lg mt-8">
                <i class="fas fa-clipboard-list text-gray-400 text-5xl mb-4"></i>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No surveys yet</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new survey.</p>
                <div class="mt-6">
                    <a href="create-survey.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-plus -ml-1 mr-2"></i>
                        Create New Survey
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteSurvey(surveyId) {
    if (confirm('Are you sure you want to delete this survey? This action cannot be undone.')) {
        window.location.href = `delete-survey.php?id=${surveyId}`;
    }
}
</script>

<?php include '../includes/footer.php'; ?>