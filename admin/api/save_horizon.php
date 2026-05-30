<?php
// web/admin/api/save_horizon.php
session_start();
require_once '../../api/db_config.php';
require_once 'image_helper.php'; // Add helper

// Security check: Only allow logged-in admins
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_logged_in'])) {
    
    // Collect data from the form
    $id = $_POST['id'] ?? ''; // Present during Edit, empty during Add
    $title = $_POST['title'];
    $label = $_POST['label'] ?? ''; 
    $caption = $_POST['caption'];
    
    // Naming rule: "image name will title of horizon"
    $clean_title = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($title));
    $horizon_dir = "../../assets/Images/horizon/";

    // Ensure directory exists
    if (!is_dir($horizon_dir)) {
        mkdir($horizon_dir, 0777, true);
    }

    try {
        if (!empty($id)) {
            // ==================== UPDATE LOGIC ====================
            if (!empty($_FILES['horizon_image']['name'])) {
                $oldImgStmt = $pdo->prepare("SELECT image FROM web_horizons WHERE id = ?");
                $oldImgStmt->execute([$id]);
                $oldImage = $oldImgStmt->fetchColumn();

                // Force .webp extension
                $filename = $clean_title . '_' . time() . '.webp'; 
                $target_path = $horizon_dir . $filename;

                // Use secure helper
                if (secure_compress_and_save($_FILES['horizon_image']['tmp_name'], $target_path)) {
                    if ($oldImage && file_exists($horizon_dir . $oldImage)) {
                        unlink($horizon_dir . $oldImage);
                    }
                    
                    $stmt = $pdo->prepare("UPDATE web_horizons SET title = ?, label = ?, caption = ?, image = ? WHERE id = ?");
                    $stmt->execute([$title, $label, $caption, $filename, $id]);
                }
            } else {
                // Update text content only
                $stmt = $pdo->prepare("UPDATE web_horizons SET title = ?, label = ?, caption = ? WHERE id = ?");
                $stmt->execute([$title, $label, $caption, $id]);
            }
            $msg = "updated=1";
        } else {
            // ==================== INSERT LOGIC ====================
            $filename = $clean_title . '_' . time() . '.webp'; // Use timestamp to avoid collisions
            $target_path = $horizon_dir . $filename;

            // Use secure helper
            if (secure_compress_and_save($_FILES['horizon_image']['tmp_name'], $target_path)) {
                $stmt = $pdo->prepare("INSERT INTO web_horizons (title, label, caption, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $label, $caption, $filename]);
            }
            $msg = "success=1";
        }
        
        header("Location: ../manage_horizons.php?$msg");
        exit;
        
    } catch (PDOException $e) {
        error_log("Horizon Save Error: " . $e->getMessage()); // Hide error details
        die("A technical error occurred. Please try again.");
    }
} else {
    header("Location: ../../index.php");
    exit;
}
?>