<?php
require_once 'config.php';

// Function to sanitize input data
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// User Authentication Functions
function registerUser($username, $email, $password) {
    global $conn;
    
    $username = sanitizeInput($username);
    $email = sanitizeInput($email);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    
    try {
        if ($stmt->execute()) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function loginUser($email, $password) {
    global $conn;
    
    $email = sanitizeInput($email);
    
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
    }
    return false;
}

function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: /login.php");
    exit();
}

// Survey Functions
function createSurvey($title, $description) {
    global $conn;
    requireAdmin(); // Only admins can create surveys
    
    $title = sanitizeInput($title);
    $description = sanitizeInput($description);
    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO surveys (title, description, created_by, status) VALUES (?, ?, ?, 'draft')");
    $stmt->bind_param("ssi", $title, $description, $userId);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function publishSurvey($surveyId) {
    global $conn;
    requireAdmin();
    
    $stmt = $conn->prepare("UPDATE surveys SET status = 'published' WHERE survey_id = ? AND created_by = ?");
    $stmt->bind_param("ii", $surveyId, $_SESSION['user_id']);
    return $stmt->execute();
}

function getSurveys($includeAll = false) {
    global $conn;
    
    $sql = "SELECT s.*, u.username as creator FROM surveys s 
            LEFT JOIN users u ON s.created_by = u.id";
    
    if (!$includeAll && !isAdmin()) {
        $sql .= " WHERE s.status = 'published'";
    }
    
    $sql .= " ORDER BY s.created_at DESC";
            
    $result = $conn->query($sql);
    
    $surveys = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $surveys[] = $row;
        }
    }
    return $surveys;
}

function getSurveyById($surveyId) {
    global $conn;
    
    $surveyId = (int)$surveyId;
    
    $stmt = $conn->prepare("
        SELECT s.*, q.*, u.username as creator 
        FROM surveys s 
        LEFT JOIN questions q ON s.survey_id = q.survey_id 
        LEFT JOIN users u ON s.created_by = u.id 
        WHERE s.survey_id = ? AND (s.status = 'published' OR ? = TRUE)
    ");
    
    $isAdmin = isAdmin();
    $stmt->bind_param("ii", $surveyId, $isAdmin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $survey = null;
    $questions = [];
    
    while ($row = $result->fetch_assoc()) {
        if (!$survey) {
            $survey = [
                'survey_id' => $row['survey_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'status' => $row['status'],
                'creator' => $row['creator'],
                'created_at' => $row['created_at']
            ];
        }
        
        if ($row['question_id']) {
            $questions[] = [
                'question_id' => $row['question_id'],
                'question_text' => $row['question_text'],
                'question_type' => $row['question_type'],
                'options' => json_decode($row['options'], true),
                'required' => $row['required'],
                'order_index' => $row['order_index']
            ];
        }
    }
    
    if ($survey) {
        $survey['questions'] = $questions;
        return $survey;
    }
    return null;
}

function addQuestion($surveyId, $questionText, $questionType, $options = null, $required = true, $orderIndex = 0) {
    global $conn;
    requireAdmin();
    
    $questionText = sanitizeInput($questionText);
    $options = $options ? json_encode($options) : null;
    
    $stmt = $conn->prepare("
        INSERT INTO questions (survey_id, question_text, question_type, options, required, order_index) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("isssii", $surveyId, $questionText, $questionType, $options, $required, $orderIndex);
    return $stmt->execute();
}

function saveSurveyResponse($surveyId, $questionId, $response) {
    global $conn;
    requireLogin(); // Must be logged in to submit responses
    
    $userId = $_SESSION['user_id'];
    $response = sanitizeInput($response);
    
    $stmt = $conn->prepare("INSERT INTO responses (survey_id, question_id, user_id, response_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $surveyId, $questionId, $userId, $response);
    
    return $stmt->execute();
}

function getResponsesBySurvey($surveyId) {
    global $conn;
    requireAdmin(); // Only admins can view all responses
    
    $stmt = $conn->prepare("
        SELECT r.*, q.question_text, q.question_type, u.username
        FROM responses r
        JOIN questions q ON r.question_id = q.question_id
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.survey_id = ?
        ORDER BY r.submitted_at DESC
    ");
    
    $stmt->bind_param("i", $surveyId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $responses = [];
    while ($row = $result->fetch_assoc()) {
        $responses[] = $row;
    }
    
    return $responses;
}

// Error handling function
function displayError($message) {
    return "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>
        <span class='block sm:inline'>$message</span>
    </div>";
}

// Success message function
function displaySuccess($message) {
    return "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative' role='alert'>
        <span class='block sm:inline'>$message</span>
    </div>";
}
?>