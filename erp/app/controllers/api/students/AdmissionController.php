<?php

require_once __DIR__ . '/../../../models/api/students/AdmissionModel.php';

class AdmissionController
{
    private $model;

    public function __construct()
    {
        $this->model = new AdmissionModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/admission/index.php';
    }
}

?>