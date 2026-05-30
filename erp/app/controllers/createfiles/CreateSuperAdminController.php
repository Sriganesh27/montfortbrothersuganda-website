<?php

require_once __DIR__ . '/../../models/createfiles/CreateSuperAdminModel.php';

class CreateSuperAdminController
{
    private $model;

    public function __construct()
    {
        $this->model = new CreateSuperAdminModel();
    }

    public function index()
    {
        include __DIR__ . '/../../views/createfiles/create_super_admin/index.php';
    }
}

?>