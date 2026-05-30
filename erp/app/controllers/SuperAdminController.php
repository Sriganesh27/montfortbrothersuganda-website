<?php

require_once __DIR__ . '/../models/SuperAdminModel.php';

class SuperAdminController
{
    private $model;

    public function __construct()
    {
        $this->model = new SuperAdminModel();
    }

    public function index()
    {
        include __DIR__ . '/../views/super_admin/index.php';
    }
}

?>