<?php

require_once __DIR__ . '/../../../models/api/students/FetchAlumniReceiptsModel.php';

class FetchAlumniReceiptsController
{
    private $model;

    public function __construct()
    {
        $this->model = new FetchAlumniReceiptsModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/fetch_alumni_receipts/index.php';
    }
}

?>