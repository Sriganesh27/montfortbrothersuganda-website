<?php

require_once __DIR__ . '/../../../models/api/super_admin/ManageSchoolsModel.php';

class ManageSchoolsController
{
    private $model;

    public function __construct()
    {
        $this->model = new ManageSchoolsModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/super_admin/manage_schools/index.php';
    }
}

?>