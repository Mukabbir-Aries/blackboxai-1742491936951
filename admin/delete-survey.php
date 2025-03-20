<?php
require_once '../includes/functions.php';

// Require admin privileges
requireAdmin();

// Get survey ID from URL
$surveyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$surveyId) {
    $_SESSION['error'] = "Invalid survey ID.";
    header("Location: dashboard.php");
    exit();
}

// Verify survey exists and belongs to the admin
$stmt = $conn->prepare("SELECT survey_id FROM surveys WHERE survey_id = ? AND created_by = ?");
$stmt->bind_param("ii", $surveyId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Survey not found or you don't have permission to delete it.";
    header("Location: dashboard.php");
    exit();
}

// Delete the survey (cascading delete will handle questions and responses)
$stmt = $conn->prepare("DELETE FROM surveys WHERE survey_id = ? AND created_by = ?");
$stmt->bind_param("ii", $surveyId, $_SESSION['user_id']);

if ($stmt->execute()) {
    $_SESSION['success'] = "Survey deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete survey. Please try again.";
}

header("Location: dashboard.php");
exit();
?>