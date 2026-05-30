<?php

require_once __DIR__ . '/../../../models/modules/students/StudentprofileModel.php';

class StudentprofileController
{
    private $model;

    public function __construct()
    {
        $this->model = new StudentprofileModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/modules/students/studentprofile/index.php';
    }
}

?>