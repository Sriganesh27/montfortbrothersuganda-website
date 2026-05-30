<?php

require_once __DIR__ . '/../../../models/modules/students/StudentmanagementModel.php';

class StudentmanagementController
{
    private $model;

    public function __construct()
    {
        $this->model = new StudentmanagementModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/modules/students/studentmanagement/index.php';
    }
}

?>