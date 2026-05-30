<?php

require_once __DIR__ . '/../../../models/modules/super_admin/SchoolManagementModel.php';

class SchoolManagementController
{
    private $model;

    public function __construct()
    {
        $this->model = new SchoolManagementModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/modules/super_admin/school_management/index.php';
    }
}

?>