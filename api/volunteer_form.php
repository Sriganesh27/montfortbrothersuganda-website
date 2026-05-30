<?php
session_start();
require_once '../includes/security.php';
require_once 'db_config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Validate CSRF Token to prevent cross-site request forgery
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security token validation failed. Please refresh the page and try again.");
    }

    // 2. Capture and sanitize form data
    $full_name     = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone_number  = trim($_POST['number'] ?? '');
    $skill         = trim($_POST['skill-category'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $experience    = trim($_POST['experience'] ?? '');
    $exp_years     = trim($_POST['experience-years'] ?? '');
    $message       = trim($_POST['intent'] ?? '');

    // 3. Basic Validation
    if (empty($full_name) || empty($email) || empty($phone_number) || empty($skill) || empty($qualification)) {
        die("Please fill in all required fields.");
    }

    // 4. Insert into the database (Using the correct table name: web_volunteer_form)
    try {
        $sql = "INSERT INTO web_volunteer_form 
                (full_name, email, phone_number, skill, qualification, experience, exp_years, message) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $full_name, 
            $email, 
            $phone_number, 
            $skill, 
            $qualification, 
            $experience, 
            $exp_years, 
            $message
        ]);

        // 5. Redirect back to the form section
        header("Location: ../unite.php?volunteer_success=1#unite-volunteer");
        exit;

    } catch (PDOException $e) {
        error_log("Volunteer Form SQL Error: " . $e->getMessage());
        die("An error occurred while submitting your application. Please try again later.");
    }
} else {
    header("Location: ../unite.php");
    exit;
}
?>