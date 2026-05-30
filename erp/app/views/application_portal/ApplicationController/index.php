<?php
// erp/app/views/application_portal/ApplicationController/index.php

// DO NOT ADD HEADER OR FOOTER INCLUDES HERE!
// This file is an API endpoint and must output pure JSON data only.

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Force the server to respond with pure JSON
header('Content-Type: application/json');

try {
    // 1. FIXED PATHS: Go up 4 folders to securely load your main config
    require_once __DIR__ . '/../../../../config/config.php';
    
    // 2. FIXED PATHS: Load the Application Model safely from its adjacent folder
    require_once __DIR__ . '/../ApplicationModel/index.php';

    // 3. Connect using the secure constants from config.php
    $conn = get_db_connection(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) throw new Exception('Database connection failed.');

    $appModel = new ApplicationModel($conn);
    $action = $_REQUEST['action'] ?? ''; 

    if ($action === 'fetch_branches') {
        echo json_encode(['success' => true, 'data' => $appModel->getActiveBranches()]); exit;
    }
    elseif ($action === 'submit_application') {
        
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security Error: Invalid submission token. Please refresh.");
        }

        $branch_id = (int)($_POST['branch_id'] ?? 0);
        $year = $_POST['admission_year'] ?? date('Y');
        list($class_code, $class_name) = array_pad(explode('|', $_POST['class_selection'] ?? ''), 2, '');
        $ref_number = $appModel->generateReferenceNumber($branch_id, $year);

        // 1. Get Branch Name for folder structure
        $stmt_b = $conn->prepare("SELECT branch_name FROM erp_branches WHERE branch_id = ?");
        $stmt_b->bind_param("i", $branch_id);
        $stmt_b->execute();
        $b_res = $stmt_b->get_result()->fetch_assoc();
        // Sanitize branch name: replace non-alphanumeric chars with underscore
        $safe_branch_name = preg_replace('/[^A-Za-z0-9]/', '_', $b_res['branch_name'] ?? 'General_School');
        
        // Build Dynamic Path: uploads/applications/{Branch}/{Class}/{Ref}/
        $target_dir = __DIR__ . "/../../../../public/assets/uploads/applications/{$safe_branch_name}/{$class_code}/{$ref_number}";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }

        // 2. Process Dynamic Subjects
        $subjects = []; 
        $s_names = $_POST['subject_name'] ?? []; 
        $s_marks = $_POST['subject_mark'] ?? [];
        $s_grades = $_POST['subject_grade'] ?? [];

        if (is_array($s_names)) {
            for ($i = 0; $i < count($s_names); $i++) { 
                if (!empty(trim($s_names[$i]))) {
                    $subjects[] = [
                        'subject' => trim($s_names[$i]), 
                        'mark' => trim($s_marks[$i] ?? ''), 
                        'grade' => trim($s_grades[$i] ?? '')
                    ]; 
                }
            }
        }

        // 3. Process S1 PLE Subjects Specifically
        if ($class_code === 'S1' && isset($_POST['ple_grades'])) {
            foreach ($_POST['ple_grades'] as $subj => $grade) {
                if (!empty(trim($grade))) {
                    $subjects[] = ['subject' => $subj, 'grade' => trim($grade)];
                }
            }
        }

        // 4. Process Student Photo (Strict 50KB Limit)
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['photo']['size'] > 51200) { // 50KB = 51200 Bytes
                throw new Exception("The student photograph must be 50KB or smaller.");
            }
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $file_name = "{$ref_number}.{$ext}";
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . '/' . $file_name)) {
                $photo_path = "/MBU_Website/Website/web/web/erp/public/assets/uploads/applications/{$safe_branch_name}/{$class_code}/{$ref_number}/{$file_name}";
            }
        }

        // 5. Process Previous Marks Document
        $prev_marks_path = null;
        if (isset($_FILES['prev_marks_doc']) && $_FILES['prev_marks_doc']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['prev_marks_doc']['name'], PATHINFO_EXTENSION));
            $file_name = "{$ref_number}_prev_marks.{$ext}";
            if (move_uploaded_file($_FILES['prev_marks_doc']['tmp_name'], $target_dir . '/' . $file_name)) {
                $prev_marks_path = "/MBU_Website/Website/web/web/erp/public/assets/uploads/applications/{$safe_branch_name}/{$class_code}/{$ref_number}/{$file_name}";
            }
        }

        // Package Data
        $appData = [
            'ref' => $ref_number, 'branch' => $branch_id, 'year' => $year, 'term' => $_POST['term'], 'reg_date' => $_POST['reg_date'] ?? date('Y-m-d'),
            'name' => $_POST['name'], 'mname' => $_POST['middle_name'], 'surname' => $_POST['surname'], 'gender' => $_POST['gender'], 'dob' => $_POST['dob'], 'nationality' => $_POST['nationality'],
            'postal' => $_POST['postal'], 'house' => $_POST['house'], 'street' => $_POST['street'], 'village' => $_POST['village'], 'district' => $_POST['district'], 'state' => $_POST['state'], 'country' => 'Uganda',
            'f_name' => $_POST['f_name'], 'f_age' => !empty($_POST['f_age']) ? $_POST['f_age'] : null, 'f_con' => $_POST['f_con'], 'f_email' => $_POST['f_email'], 'f_occ' => $_POST['f_occ'], 'f_edu' => $_POST['f_edu'],
            'm_name' => $_POST['m_name'], 'm_age' => !empty($_POST['m_age']) ? $_POST['m_age'] : null, 'm_con' => $_POST['m_con'], 'm_email' => $_POST['m_email'], 'm_occ' => $_POST['m_occ'], 'm_edu' => $_POST['m_edu'],
            'g_name' => $_POST['g_name'], 'g_rel' => $_POST['g_rel'], 'g_age' => !empty($_POST['g_age']) ? $_POST['g_age'] : null, 'g_con' => $_POST['g_con'], 'g_email' => $_POST['g_email'], 'g_occ' => $_POST['g_occ'], 'g_edu' => $_POST['g_edu'],
            'level' => $_POST['level'], 'class_name' => $class_name, 'class_code' => $class_code, 
            'former_school' => $_POST['former_school'], 'former_school_code' => $_POST['former_school_code'], 'former_school_lin' => $_POST['former_school_lin'], 'prev_marks_doc' => $prev_marks_path,
            'ple_score' => $_POST['ple_score'] ?? null, 'ple_ref' => $_POST['ple_ref'] ?? null, 'uce_score' => $_POST['uce_score'] ?? null, 'uce_ref' => $_POST['uce_ref'] ?? null,
            'subject_marks' => json_encode($subjects), 'more_info' => $_POST['more_info'], 'photo_path' => $photo_path
        ];

        if ($appModel->saveApplication($appData)) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
            echo json_encode(['success' => true, 'ref_number' => $ref_number]); 
        } else {
            throw new Exception("Database failed to execute.");
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (Throwable $e) { 
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]); 
}
?>