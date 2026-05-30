<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/StudentMarksViewModel.php';

class StudentMarksViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new StudentMarksViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/student_marks_view/index.php';
    }
}

?>