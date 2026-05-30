<?php

require_once __DIR__ . '/../../../models/modules/super_admin/CredentialManagerModel.php';

class CredentialManagerController
{
    private $model;

    public function __construct()
    {
        $this->model = new CredentialManagerModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/modules/super_admin/credential_manager/index.php';
    }
}

?>