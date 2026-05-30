<?php

require_once __DIR__ . '/../../../models/api/students/FetchAcademicEditModel.php';

class FetchAcademicEditController
{
    private $model;

    public function __construct()
    {
        $this->model = new FetchAcademicEditModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/fetch_academic_edit/index.php';
    }
}

?>