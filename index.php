<?php
require_once 'includes/functions.php';
include 'includes/header.php';

// Get all published surveys
$surveys = getSurveys();
?>

<!-- Hero Section -->
<div class="bg-white">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                <span class="block">Welcome to Task Buddy</span>
                <span class="block text-blue-600">Your Survey Platform</span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Create, share, and participate in surveys. Get valuable insights from your audience with our easy-to-use platform.
            </p>
            <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                <?php if (!isLoggedIn()): ?>
                <div class="rounded-md shadow">
                    <a href="/register.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10">
                        Get Started
                    </a>
                </div>
                <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                    <a href="/login.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                        Sign In
                    </a>
                </div>
                <?php else: ?>
                <div class="rounded-md shadow">
                    <a href="/surveys.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10">
                        View Surveys
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Featured Surveys Section -->
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">
                Featured Surveys
            </h2>
            <p class="mt-4 text-lg text-gray-500">
                Participate in our latest surveys and make your voice heard
            </p>
        </div>

        <?php if (!empty($surveys)): ?>
        <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($surveys as $survey): ?>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-poll text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                <?php echo htmlspecialchars($survey['title']); ?>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Created by <?php echo htmlspecialchars($survey['creator']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-base text-gray-500">
                            <?php echo htmlspecialchars(substr($survey['description'], 0, 150)) . '...'; ?>
                        </p>
                    </div>
                    <div class="mt-6">
                        <a href="/survey.php?id=<?php echo $survey['survey_id']; ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Take Survey
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="mt-12 text-center">
            <p class="text-gray-500">No surveys available at the moment.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Features Section -->
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">
                Why Choose Task Buddy?
            </h2>
        </div>

        <div class="mt-10">
            <div class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                <!-- Feature 1 -->
                <div class="relative">
                    <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Easy to Use</p>
                    <p class="mt-2 ml-16 text-base text-gray-500">
                        Create and share surveys in minutes with our intuitive interface.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="relative">
                    <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Real-time Results</p>
                    <p class="mt-2 ml-16 text-base text-gray-500">
                        Get instant insights with our real-time analytics dashboard.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="relative">
                    <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Secure & Private</p>
                    <p class="mt-2 ml-16 text-base text-gray-500">
                        Your data is protected with enterprise-grade security.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>