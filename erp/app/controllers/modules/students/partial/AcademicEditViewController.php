<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/AcademicEditViewModel.php';

class AcademicEditViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new AcademicEditViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/academic_edit_view/index.php';
    }
}

?>