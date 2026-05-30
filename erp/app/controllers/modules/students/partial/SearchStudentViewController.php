<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/SearchStudentViewModel.php';

class SearchStudentViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new SearchStudentViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/search_student_view/index.php';
    }
}

?>