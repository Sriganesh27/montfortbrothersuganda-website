<?php
// web/admin/api/save_mission.php
session_start();
require_once '../../api/db_config.php';
require_once 'image_helper.php'; // Add helper

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin_logged_in'])) {
    $id = $_POST['id'] ?? '';
    $content = trim($_POST['content']);
    $upload_dir = "../../assets/Images/mission/";

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    try {
        $pdo->beginTransaction();

        // 1. Handle Record Creation
        if (empty($id)) {
            $stmt = $pdo->prepare("INSERT INTO web_uganda_mission (content, images, is_active) VALUES (?, '', 1)");
            $stmt->execute([$content]);
            $id = $pdo->lastInsertId();
        }

        // 2. Process New Image Uploads
        $uploaded_files = [];
        $has_new_images = !empty($_FILES['mission_images']['name'][0]);

        if ($has_new_images) {
            $oldImgStmt = $pdo->prepare("SELECT images FROM web_uganda_mission WHERE id = ?");
            $oldImgStmt->execute([$id]);
            $old_data = $oldImgStmt->fetchColumn();
            
            $existing_images_array = [];
            $max_index = 0;

            if ($old_data) {
                $existing_images_array = array_filter(explode(',', $old_data));
                foreach ($existing_images_array as $img_name) {
                    if (preg_match('/um_\d+_(\d+)\./', $img_name, $matches)) {
                        if ((int)$matches[1] > $max_index) {
                            $max_index = (int)$matches[1];
                        }
                    }
                }
            }

            // Loop through files and compress
            $files = $_FILES['mission_images'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

                $max_index++; 
                // Force .webp extension
                $new_name = "um_" . $id . "_" . $max_index . ".webp"; 
                
                // Use secure helper
                if (secure_compress_and_save($files['tmp_name'][$i], $upload_dir . $new_name)) {
                    $uploaded_files[] = $new_name;
                }
            }

            $final_images_array = array_merge($existing_images_array, $uploaded_files);
            $final_images_string = implode(',', $final_images_array);

            $stmt = $pdo->prepare("UPDATE web_uganda_mission SET content = ?, images = ? WHERE id = ?");
            $stmt->execute([$content, $final_images_string, $id]);
            
        } else {
            $stmt = $pdo->prepare("UPDATE web_uganda_mission SET content = ? WHERE id = ?");
            $stmt->execute([$content, $id]);
        }

        $pdo->commit();
        header("Location: ../manage_mission.php?status=success");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Mission Save Error: " . $e->getMessage()); // Hide error details
        header("Location: ../manage_mission.php?status=error");
    }
    exit;
}
?>