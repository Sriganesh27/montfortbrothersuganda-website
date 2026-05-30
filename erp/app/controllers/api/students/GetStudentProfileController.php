<?php

require_once __DIR__ . '/../../../models/api/students/GetStudentProfileModel.php';

class GetStudentProfileController
{
    private $model;

    public function __construct()
    {
        $this->model = new GetStudentProfileModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/get_student_profile/index.php';
    }
}

?>