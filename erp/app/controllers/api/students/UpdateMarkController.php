<?php

require_once __DIR__ . '/../../../models/api/students/UpdateMarkModel.php';

class UpdateMarkController
{
    private $model;

    public function __construct()
    {
        $this->model = new UpdateMarkModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/update_mark/index.php';
    }
}

?>