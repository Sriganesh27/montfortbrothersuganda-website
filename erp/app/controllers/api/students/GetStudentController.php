<?php

require_once __DIR__ . '/../../../models/api/students/GetStudentModel.php';

class GetStudentController
{
    private $model;

    public function __construct()
    {
        $this->model = new GetStudentModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/get_student/index.php';
    }
}

?>