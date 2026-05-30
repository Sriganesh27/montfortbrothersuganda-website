<?php

require_once __DIR__ . '/../../../models/api/students/MigrateBackendModel.php';

class MigrateBackendController
{
    private $model;

    public function __construct()
    {
        $this->model = new MigrateBackendModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/migrate_backend/index.php';
    }
}

?>