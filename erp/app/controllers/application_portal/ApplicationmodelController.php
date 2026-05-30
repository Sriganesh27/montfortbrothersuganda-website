<?php

require_once __DIR__ . '/../../models/application_portal/ApplicationmodelModel.php';

class ApplicationmodelController
{
    private $model;

    public function __construct()
    {
        $this->model = new ApplicationmodelModel();
    }

    public function index()
    {
        include __DIR__ . '/../../views/application_portal/ApplicationModel/index.php';
    }
}

?>