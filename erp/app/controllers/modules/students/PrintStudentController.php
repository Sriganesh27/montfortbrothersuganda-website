<?php

require_once __DIR__ . '/../../../models/modules/students/PrintStudentModel.php';

class PrintStudentController
{
    private $model;

    public function __construct()
    {
        $this->model = new PrintStudentModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/modules/students/print_student/index.php';
    }
}

?>