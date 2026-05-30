<?php

require_once __DIR__ . '/../../../models/api/students/FetchAlumniDebtsModel.php';

class FetchAlumniDebtsController
{
    private $model;

    public function __construct()
    {
        $this->model = new FetchAlumniDebtsModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/fetch_alumni_debts/index.php';
    }
}

?>