<?php

require_once __DIR__ . '/../../../models/api/students/FetchStudentAccountsModel.php';

class FetchStudentAccountsController
{
    private $model;

    public function __construct()
    {
        $this->model = new FetchStudentAccountsModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/fetch_student_accounts/index.php';
    }
}

?>