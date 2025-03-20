<?php
require_once 'includes/functions.php';

// Require login to view surveys
requireLogin();

// Get all published surveys
$surveys = getSurveys();

include 'includes/header.php';
?>

<div class="bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:text-4xl sm:truncate">
                    Available Surveys
                </h2>
                <p class="mt-1 text-lg text-gray-500">
                    Browse and participate in our collection of surveys
                </p>
            </div>
            <?php if (isAdmin()): ?>
            <div class="mt-5 flex lg:mt-0 lg:ml-4">
                <a href="/admin/create-survey.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-plus -ml-1 mr-2"></i>
                    Create New Survey
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Survey Filters -->
        <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex-1">
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchSurvey" 
                           class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md" 
                           placeholder="Search surveys...">
                </div>
            </div>
        </div>

        <!-- Survey List -->
        <?php if (!empty($surveys)): ?>
        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <?php foreach ($surveys as $survey): ?>
            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300 ease-in-out">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <?php echo htmlspecialchars($survey['title']); ?>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Created by <?php echo htmlspecialchars($survey['creator']); ?> • 
                                <?php echo date('M d, Y', strtotime($survey['created_at'])); ?>
                            </p>
                        </div>
                        <div class="ml-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                    
                    <p class="mt-4 text-base text-gray-500">
                        <?php echo htmlspecialchars($survey['description']); ?>
                    </p>
                    
                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                <span>5-10 minutes</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-users mr-1"></i>
                                <span>Open to all</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3">
                            <a href="/survey.php?id=<?php echo $survey['survey_id']; ?>" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Take Survey
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                            
                            <?php if (isAdmin()): ?>
                            <a href="/admin/edit-survey.php?id=<?php echo $survey['survey_id']; ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-2"></i>
                                Edit
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="mt-8 text-center py-12 bg-gray-50 rounded-lg">
            <div class="inline-block">
                <i class="fas fa-clipboard-list text-gray-400 text-5xl mb-4"></i>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No surveys available</h3>
            <p class="mt-1 text-sm text-gray-500">
                There are no active surveys at the moment.
            </p>
            <?php if (isAdmin()): ?>
            <div class="mt-6">
                <a href="/admin/create-survey.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus -ml-1 mr-2"></i>
                    Create New Survey
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Simple search functionality
document.getElementById('searchSurvey').addEventListener('input', function(e) {
    const searchText = e.target.value.toLowerCase();
    const surveyCards = document.querySelectorAll('.grid > div');
    
    surveyCards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const description = card.querySelector('p:nth-child(2)').textContent.toLowerCase();
        
        if (title.includes(searchText) || description.includes(searchText)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>