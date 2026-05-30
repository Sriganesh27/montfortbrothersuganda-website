<?php
// erp/app/controllers/ApplicationcontrollerController.php

// 1. Safely load the model from its subfolder
$model_path = __DIR__ . '/../models/application_portal/ApplicationcontrollerModel.php';
if (file_exists($model_path)) {
    require_once $model_path;
}

class ApplicationcontrollerController
{
    private $model;

    public function __construct()
    {
        if (class_exists('ApplicationcontrollerModel')) {
            $this->model = new ApplicationcontrollerModel();
        }
    }

    public function index()
    {
        // 2. Force JSON output to prevent Javascript from crashing
        header('Content-Type: application/json');
        
        // Hide PHP warnings that might accidentally print HTML text
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // 3. Include your actual API processing script
        $api_file = __DIR__ . '/../views/application_portal/ApplicationController/index.php';
        
        if (file_exists($api_file)) {
            include $api_file;
        } else {
            // If the file is missing, output a clean JSON error instead of crashing
            echo json_encode([
                'success' => false, 
                'message' => 'API script not found at: ' . $api_file
            ]);
        }
        
        exit; // Stop everything so no extra HTML is appended
    }
}
?>