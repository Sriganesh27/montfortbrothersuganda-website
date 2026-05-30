<?php

require_once __DIR__ . '/../../../models/api/students/ManageAccountModel.php';

class ManageAccountController
{
    private $model;

    public function __construct()
    {
        $this->model = new ManageAccountModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/manage_account/index.php';
    }
}

?>