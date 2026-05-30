<?php

require_once __DIR__ . '/../../models/includes/SidebarModel.php';

class SidebarController
{
    private $model;

    public function __construct()
    {
        $this->model = new SidebarModel();
    }

    public function index()
    {
        include __DIR__ . '/../../views/includes/sidebar/index.php';
    }
}

?>