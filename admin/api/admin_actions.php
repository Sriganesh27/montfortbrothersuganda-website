<?php
/**
 * web/admin/api/admin_actions.php
 */

// 1. Include security headers and session configuration
require_once '../../includes/security.php';

// 2. Security Check: Only logged-in admins
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// 3. CSRF Protection: Validate the security token for state-changing actions
function validate_admin_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Strictly enforce POST method and validate token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$provided_token = $_POST['csrf_token'] ?? '';
if (!validate_admin_csrf($provided_token)) {
    http_response_code(403); // Optional: Send a Forbidden status code
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Security token mismatch. Please refresh and try again.']);
    exit;
}

// Include database configuration
require_once '../../api/db_config.php'; 

// Helper function to log admin actions
function log_admin_action($pdo, $admin_id, $action, $table, $target_id) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt = $pdo->prepare("INSERT INTO web_audit_logs (admin_id, action_type, target_table, target_id, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$admin_id, $action, $table, $target_id, $ip]);
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$table  = $_POST['table'] ?? '';
$id     = intval($_POST['id'] ?? 0);

// 4. Security: Whitelist allowed tables to prevent SQL Injection via table name
$allowed_tables = [
    'web_users', 
    'web_donations', 
    'web_projects', 
    'web_contact_inquiries', 
    'web_volunteer_form', 
    'web_horizons', 
    'web_school_results', 
    'web_gallery_highlights', 
    'web_uganda_mission'
];

if (!in_array($table, $allowed_tables)) {
    echo json_encode(['success' => false, 'message' => 'Invalid table request.']);
    exit;
}
// Helper function to clear frontend cache so updates show instantly
function clear_frontend_cache() {
    $cache_dir = '../../cache/'; // Path to your web/cache folder
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '*.json'); // Get all cache files
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file); // Delete the cache file
            }
        }
    }
}
// ==================== 1. TOGGLE STATUS ====================
if ($action === 'toggle_status') {
    try {
        // Toggles the is_active boolean (1 to 0 or 0 to 1)
        $stmt = $pdo->prepare("UPDATE `$table` SET is_active = NOT is_active WHERE id = ?");
        if ($stmt->execute([$id])) {
            // ⭐ LOG THE ACTION
            log_admin_action($pdo, $_SESSION['admin_id'], 'TOGGLE_STATUS', $table, $id);
            echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
        }
    } catch (PDOException $e) {
        // Logging error privately instead of echoing to prevent info disclosure
        error_log("Status Toggle Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
    }
    exit;
}

// ==================== 2. DELETE INDEPENDENT MISSION IMAGE ====================
if ($action === 'delete_mission_image') {
    // SECURITY FIX: basename() prevents path traversal attacks
    $image_name = basename($_POST['image_name'] ?? ''); 
    
    if (empty($image_name)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file name.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT images FROM web_uganda_mission WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if ($row) {
            $images = explode(',', $row['images']);
            $new_images = array_filter($images, function($img) use ($image_name) {
                return trim($img) !== trim($image_name);
            });

            $stmt = $pdo->prepare("UPDATE web_uganda_mission SET images = ? WHERE id = ?");
            if ($stmt->execute([implode(',', $new_images), $id])) {
                $path = "../../assets/Images/mission/" . $image_name;
                if (file_exists($path)) {
                    unlink($path);
                }
                // ⭐ LOG THE ACTION
                log_admin_action($pdo, $_SESSION['admin_id'], 'DELETE_IMAGE', 'web_uganda_mission', $id);
                echo json_encode(['success' => true, 'message' => 'Image deleted successfully.']);
            }
        }
    } catch (Exception $e) {
        error_log("Mission Image Delete Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting image.']);
    }
    exit;
}

// ==================== 3. DELETE FULL RECORD ====================
if ($action === 'delete') {
    // Prevent admin from accidentally deleting themselves
    if ($table === 'web_users' && $id === ($_SESSION['admin_id'] ?? 0)) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own admin account while logged in!']);
        exit;
    }

    try {
        $folder = "";
        $column = "image"; // Default
        
        if ($table === 'web_uganda_mission') {
            $folder = "mission";
            $column = "images";
        } elseif ($table === 'web_horizons') {
            $folder = "horizon";
        } elseif ($table === 'web_gallery_highlights') {
            $folder = "gallery_highlights";
        }

        // Fetch filenames for physical cleanup
        $files_to_delete = [];
        if ($folder !== "") {
            $stmt = $pdo->prepare("SELECT `$column` FROM `$table` WHERE id = ?");
            $stmt->execute([$id]);
            $rawData = $stmt->fetchColumn();
            
            if ($rawData) {
                $files_to_delete = ($table === 'web_uganda_mission') ? explode(',', $rawData) : [$rawData];
            }
        }

        // Delete the record
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Delete the physical files
            foreach ($files_to_delete as $f) {
                $f = trim($f);
                if (!empty($f)) {
                    $safe_file = basename($f); 
                    $path = "../../assets/Images/$folder/" . $safe_file;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
            // LOG THE ACTION
            log_admin_action($pdo, $_SESSION['admin_id'], 'DELETE_RECORD', $table, $id);
            echo json_encode(['success' => true, 'message' => 'Record and associated files deleted successfully.']);
        }
    } catch (PDOException $e) {
        error_log("Full Deletion Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error during deletion.']);
    }
    exit;
}

// ==================== 4. UPDATE DISPLAY ORDER ====================
if ($action === 'update_order') {
    $orderData = json_decode($_POST['order_data'] ?? '[]', true);
    $sortable_tables = ['web_gallery_highlights', 'web_horizons', 'web_uganda_mission'];
    
    if (in_array($table, $sortable_tables) && is_array($orderData)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE `$table` SET display_order = :display_order WHERE id = :id");
            
            foreach ($orderData as $item) {
                $stmt->execute([
                    ':display_order' => (int)$item['display_order'],
                    ':id' => (int)$item['id']
                ]);
            }
            $pdo->commit();
            
            // ⭐ ADD THIS HERE: Force index.php to fetch new data immediately
            clear_frontend_cache(); 
            
            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Order Update Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error updating order.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid table or data for ordering.']);
    }
    exit;
}

// ==================== FALLBACK: INVALID ACTION ====================
// If the script reaches this point, no valid action was matched.
echo json_encode(['success' => false, 'message' => 'Invalid action provided.']);
exit; 
?>