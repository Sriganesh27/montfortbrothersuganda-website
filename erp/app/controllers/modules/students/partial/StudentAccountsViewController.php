<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/StudentAccountsViewModel.php';

class StudentAccountsViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new StudentAccountsViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/student_accounts_view/index.php';
    }
}

?>