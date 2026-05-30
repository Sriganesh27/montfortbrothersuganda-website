<?php

require_once __DIR__ . '/../../../models/api/students/UpdateAcademicEditModel.php';

class UpdateAcademicEditController
{
    private $model;

    public function __construct()
    {
        $this->model = new UpdateAcademicEditModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/update_academic_edit/index.php';
    }
}

?>