<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/MigrateViewModel.php';

class MigrateViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new MigrateViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/migrate_view/index.php';
    }
}

?>