<?php

require_once __DIR__ . '/../../../models/api/students/DeletestudentModel.php';

class DeletestudentController
{
    private $model;

    public function __construct()
    {
        $this->model = new DeletestudentModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/deletestudent/index.php';
    }
}

?>