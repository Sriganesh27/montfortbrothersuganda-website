<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/StudentsummaryModel.php';

class StudentsummaryController
{
    private $model;

    public function __construct()
    {
        $this->model = new StudentsummaryModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/studentsummary/index.php';
    }
}

?>