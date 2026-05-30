<?php

require_once __DIR__ . '/../../../models/api/students/FetchStudentsMigrationModel.php';

class FetchStudentsMigrationController
{
    private $model;

    public function __construct()
    {
        $this->model = new FetchStudentsMigrationModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/fetch_students_migration/index.php';
    }
}

?>