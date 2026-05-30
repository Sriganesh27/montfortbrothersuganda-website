<?php
// web/admin/api/save_result.php
session_start();
require_once '../../api/db_config.php'; // Defines the $pdo connection

// Security check: Only allow logged-in admins
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_logged_in'])) {
    
    // Collect and sanitize data from the form
    $id = $_POST['id'] ?? ''; // Present during Edit, empty during Add
    $year = $_POST['year'];
    $inst = $_POST['institution'];
    $course = $_POST['course'];
    $cert = $_POST['certificate'];
    $tm = (int)$_POST['train_male'];
    $tf = (int)$_POST['train_female'];
    $gm = (int)$_POST['grad_male'];
    $gf = (int)$_POST['grad_female'];

    try {
        if (!empty($id)) {
            // PERFORM UPDATE for existing record
            $stmt = $pdo->prepare("UPDATE web_school_results SET year=?, institution=?, course=?, certificate=?, train_male=?, train_female=?, grad_male=?, grad_female=? WHERE id=?");
            $stmt->execute([$year, $inst, $course, $cert, $tm, $tf, $gm, $gf, $id]);
            $msg = "updated=1";
        } else {
            // PERFORM INSERT for new record
            $stmt = $pdo->prepare("INSERT INTO web_school_results (year, institution, course, certificate, train_male, train_female, grad_male, grad_female) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$year, $inst, $course, $cert, $tm, $tf, $gm, $gf]);
            $msg = "success=1";
        }
        
        // Redirect back to the management page with a success message
        header("Location: ../manage_results.php?$msg");
        exit;
        
    } catch (PDOException $e) {
        error_log("Result Processing Error: " . $e->getMessage());
        die("A technical error occurred. Please try again.");
    }
} else {
    // Redirect if accessed directly or not logged in
    header("Location: ../../index.php");
    exit;
}
?>