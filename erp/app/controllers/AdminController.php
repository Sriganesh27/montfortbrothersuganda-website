<?php
// erp/app/controllers/AdminController.php

class AdminController
{
    private $model;

    public function __construct()
    {
        // Your autoloader will automatically find erp/app/models/AdminModel.php!
        // No require_once needed.
        $this->model = new AdminModel();
    }

    public function index()
    {
        include __DIR__ . '/../views/admin/index.php';
    }
}
?>