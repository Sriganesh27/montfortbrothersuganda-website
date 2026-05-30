<?php
// erp/app/controllers/ApplyController.php

// 1. MUST LOAD CONFIG FIRST (Provides get_db_connection and DB variables)
require_once __DIR__ . '/../../config/config.php';

// 2. THEN LOAD THE MODEL
require_once __DIR__ . '/../models/application_portal/ApplyModel.php';

class ApplyController
{
    private $model;

    public function __construct()
    {
        if (class_exists('ApplyModel')) {
            $this->model = new ApplyModel();
        }
    }

    public function index()
    {
        $view_path = __DIR__ . '/../views/application_portal/apply/index.php';
        
        if (file_exists($view_path)) {
            include $view_path;
        } else {
            echo "<h1>View Missing</h1><p>Cannot find the application portal view at: $view_path</p>";
        }
    }

    // Handles the student downloading/printing their receipt
    public function printApplication()
    {
        $ref = $_GET['ref'] ?? '';
        if (empty($ref)) { die("Error: No Reference Number Provided."); }

        // Fetch application using the Model
        $data = $this->model->getApplicationByRef($ref);
        
        if (!$data) { die("Error: Application not found or invalid Reference Number."); }

        // Load the public print view (the one without signatures)
        require_once __DIR__ . '/../views/application_portal/print_application.php';
    }
    // Loads the Track Status UI Page
    public function statusPage()
    {
        $view_path = __DIR__ . '/../views/application_portal/status.php';
        if (file_exists($view_path)) {
            require_once $view_path;
        } else {
            echo "<h1>View Missing</h1><p>Cannot find the status view.</p>";
        }
    }

    // API Endpoint: Securely checks the status based on Ref Number
    public function checkStatus()
    {
        header('Content-Type: application/json');
        $ref = $_POST['ref_number'] ?? '';
        
        if (empty($ref)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid Reference Number.']);
            return;
        }

        // Fetch application using the Model
        $data = $this->model->getApplicationByRef($ref);
        
        if ($data) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'ref_number' => htmlspecialchars($data['ref_number']),
                    'student_name' => htmlspecialchars(trim($data['student_name'] . ' ' . $data['student_surname'])),
                    'applied_class' => htmlspecialchars($data['applied_class'] . ' (' . $data['level'] . ')'),
                    'status' => htmlspecialchars($data['status'] ?? 'Pending'),
                    'scholarship_status' => htmlspecialchars($data['scholarship_status'] ?? 'None')
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Application not found. Please check your reference number and try again.']);
        }
    }
}
?>