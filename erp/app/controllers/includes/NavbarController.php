<?php

require_once __DIR__ . '/../../models/includes/NavbarModel.php';

class NavbarController
{
    private $model;

    public function __construct()
    {
        $this->model = new NavbarModel();
    }

    public function index()
    {
        include __DIR__ . '/../../views/includes/navbar/index.php';
    }
}

?>