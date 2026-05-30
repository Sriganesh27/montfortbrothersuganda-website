<?php

require_once __DIR__ . '/../../../models/assets/scripts/SecurityModel.php';

class SecurityController
{
    private $model;

    public function __construct()
    {
        $this->model = new SecurityModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/assets/scripts/security/index.php';
    }
}

?>