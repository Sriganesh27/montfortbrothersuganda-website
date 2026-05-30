<?php
// web/admin/api/save_highlight.php
session_start();
require_once '../../api/db_config.php';
require_once 'image_helper.php'; // Add helper

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_logged_in'])) {
    $id = $_POST['id'] ?? '';
    $caption = $_POST['caption'];
    $upload_dir = "../../assets/Images/gallery_highlights/";

    try {
        if (!empty($id)) {
            // UPDATE
            if (!empty($_FILES['gallery_image']['name'])) {
                // Image and Text update
                $stmt = $pdo->prepare("SELECT image FROM web_gallery_highlights WHERE id = ?");
                $stmt->execute([$id]);
                $old_img = $stmt->fetchColumn();

                // Force .webp extension
                $filename = time() . "_" . pathinfo($_FILES['gallery_image']['name'], PATHINFO_FILENAME) . ".webp";
                
                // Use secure helper
                if (secure_compress_and_save($_FILES['gallery_image']['tmp_name'], $upload_dir . $filename)) {
                    if ($old_img && file_exists($upload_dir . $old_img)) unlink($upload_dir . $old_img);
                    $stmt = $pdo->prepare("UPDATE web_gallery_highlights SET caption=?, image=? WHERE id=?");
                    $stmt->execute([$caption, $filename, $id]);
                }
            } else {
                // Text only update
                $stmt = $pdo->prepare("UPDATE web_gallery_highlights SET caption=? WHERE id=?");
                $stmt->execute([$caption, $id]);
            }
        } else {
            // INSERT
            $filename = time() . "_" . pathinfo($_FILES['gallery_image']['name'], PATHINFO_FILENAME) . ".webp";
            // Use secure helper
            if (secure_compress_and_save($_FILES['gallery_image']['tmp_name'], $upload_dir . $filename)) {
                $stmt = $pdo->prepare("INSERT INTO web_gallery_highlights (caption, image) VALUES (?, ?)");
                $stmt->execute([$caption, $filename]);
            }
        }
        header("Location: ../manage_gallery.php?success=1");
    } catch (PDOException $e) {
        error_log("Gallery Highlight Save Error: " . $e->getMessage()); // Hide error details
        header("Location: ../manage_gallery.php?error=db_fail");
        exit;
    }
}