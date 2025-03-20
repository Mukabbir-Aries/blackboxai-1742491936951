<?php 
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Buddy - Survey Platform</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-2xl font-bold text-blue-600">
                            <i class="fas fa-tasks mr-2"></i>Task Buddy
                        </a>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="/" class="inline-flex items-center px-1 pt-1 text-gray-700 hover:text-blue-600">
                            Home
                        </a>
                        <a href="/surveys.php" class="inline-flex items-center px-1 pt-1 text-gray-700 hover:text-blue-600">
                            Surveys
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="/admin/dashboard.php" class="inline-flex items-center px-1 pt-1 text-gray-700 hover:text-blue-600">
                            Admin Dashboard
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center">
                    <?php if (isLoggedIn()): ?>
                        <div class="hidden md:flex items-center space-x-4">
                            <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <a href="/logout.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                Logout
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="hidden md:flex items-center space-x-4">
                            <a href="/login.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50">
                                Login
                            </a>
                            <a href="/register.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Register
                            </a>
                        </div>
                    <?php endif; ?>
                    <!-- Mobile menu button -->
                    <div class="flex items-center md:hidden">
                        <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mobile menu -->
        <div class="hidden mobile-menu md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Home</a>
                <a href="/surveys.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Surveys</a>
                <?php if (isAdmin()): ?>
                <a href="/admin/dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Admin Dashboard</a>
                <?php endif; ?>
                <?php if (isLoggedIn()): ?>
                    <div class="border-t border-gray-200 pt-4">
                        <div class="px-3 py-2 text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <a href="/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-red-600 hover:bg-red-700">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="border-t border-gray-200 pt-4">
                        <a href="/login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">Login</a>
                        <a href="/register.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-blue-600 hover:bg-blue-700">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 py-6">
        <?php
        if (isset($_SESSION['error'])) {
            echo displayError($_SESSION['error']);
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo displaySuccess($_SESSION['success']);
            unset($_SESSION['success']);
        }
        ?>