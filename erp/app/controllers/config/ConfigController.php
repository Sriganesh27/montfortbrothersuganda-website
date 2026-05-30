<?php

require_once __DIR__ . '/../../models/config/ConfigModel.php';

class ConfigController
{
    private $model;

    public function __construct()
    {
        $this->model = new ConfigModel();
    }

    public function index()
    {
        include __DIR__ . '/../../views/config/config/index.php';
    }
}

?>