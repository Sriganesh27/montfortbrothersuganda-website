<?php

require_once __DIR__ . '/../../models/createfiles/CreateAllAdminsModel.php';

class CreateAllAdminsController
{
    private $model;

    public function __construct()
    {
        $this->model = new CreateAllAdminsModel();
    }

    public function index()
    {
        include __DIR__ . '/../../views/createfiles/create_all_admins/index.php';
    }
}

?>