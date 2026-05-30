<?php

require_once __DIR__ . '/../../../models/api/students/EditStudentModel.php';

class EditStudentController
{
    private $model;

    public function __construct()
    {
        $this->model = new EditStudentModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/edit_student/index.php';
    }
}

?>