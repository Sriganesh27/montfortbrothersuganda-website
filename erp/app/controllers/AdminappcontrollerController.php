<?php
// erp/app/controllers/AdminappcontrollerController.php

class AdminappcontrollerController
{
    private $model;

    public function __construct()
    {
        // 1. FIX: Load your configuration file so the Model can connect to the Database!
        $config_path = __DIR__ . '/../../config/config.php';
        if (file_exists($config_path)) {
            require_once $config_path;
        }

        // 2. Safely load the model
        $model_path = __DIR__ . '/../models/modules/manage_applications/AdminappcontrollerModel.php';
        if (file_exists($model_path)) {
            require_once $model_path;
            $this->model = new AdminappcontrollerModel();
        }
    }

    public function index()
    {
        // Force pure JSON output
        header('Content-Type: application/json');
        
        // Prevent any minor PHP notices from breaking the JSON output
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Start Session securely to get branch context
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $branch_id = $_SESSION['branch_id'] ?? 1; // Fallback to 1 if testing

        // Ensure Model loaded successfully
        if (!$this->model) {
            echo json_encode(['success' => false, 'message' => 'System Error: Model could not be loaded.']);
            exit;
        }

        // Handle the API Requests from Javascript
        $action = $_REQUEST['action'] ?? '';

        if ($action === 'fetch_list') {
            $status = $_GET['status'] ?? 'All';
            $data = $this->model->getApplicationsList($branch_id, $status);
            echo json_encode(['success' => true, 'data' => $data]);
            exit;
        }

        if ($action === 'fetch_single') {
            $app_id = $_GET['app_id'] ?? 0;
            $data = $this->model->getSingleApplication($app_id, $branch_id);
            if ($data) {
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Application not found.']);
            }
            exit;
        }

        if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $app_id = $_POST['app_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            $success = $this->model->updateStatus($app_id, $status, $branch_id);
            echo json_encode(['success' => $success]);
            exit;
        }

        if ($action === 'update_scholarship' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $app_id = $_POST['app_id'] ?? 0;
            $scholarship = $_POST['scholarship'] ?? '';
            $success = $this->model->updateScholarship($app_id, $scholarship, $branch_id);
            echo json_encode(['success' => $success, 'message' => 'Scholarship status updated!']);
            exit;
        }

        // Default fallback if no action matched
        echo json_encode(['success' => false, 'message' => 'Invalid action requested.']);
        exit;
    }
    public function viewApplicationPage() {
    $app_id = $_GET['id'] ?? 0;
    
    // Fetch the data using the same model method
    $data = $this->model->getSingleApplication($app_id, $_SESSION['branch_id'] ?? 1);
    
    if (!$data) { die("Application not found."); }
    
    // Pass the data to the view
    require_once __DIR__ . '/../views/modules/manage_applications/view_application.php';
}
}
?>