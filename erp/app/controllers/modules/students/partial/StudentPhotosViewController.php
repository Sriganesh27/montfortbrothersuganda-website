<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/StudentPhotosViewModel.php';

class StudentPhotosViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new StudentPhotosViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/student_photos_view/index.php';
    }
}

?>