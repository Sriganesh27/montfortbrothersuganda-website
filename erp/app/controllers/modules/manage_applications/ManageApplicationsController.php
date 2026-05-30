<?php

require_once __DIR__ . '/../../../models/modules/manage_applications/ManageApplicationsModel.php';

class ManageApplicationsController
{
    private $model;

    public function __construct()
    {
        $this->model = new ManageApplicationsModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/modules/manage_applications/manage_applications/index.php';
    }
}

?>