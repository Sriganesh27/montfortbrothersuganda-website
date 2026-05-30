<?php

require_once __DIR__ . '/../../../models/api/students/GetStudentHistoryModel.php';

class GetStudentHistoryController
{
    private $model;

    public function __construct()
    {
        $this->model = new GetStudentHistoryModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/get_student_history/index.php';
    }
}

?>