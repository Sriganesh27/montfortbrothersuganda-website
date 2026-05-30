<?php

require_once __DIR__ . '/../../../models/api/students/UpdateStudentPhotoModel.php';

class UpdateStudentPhotoController
{
    private $model;

    public function __construct()
    {
        $this->model = new UpdateStudentPhotoModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/update_student_photo/index.php';
    }
}

?>