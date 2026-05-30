<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/ViewstudentsModel.php';

class ViewstudentsController
{
    private $model;

    public function __construct()
    {
        $this->model = new ViewstudentsModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/viewstudents/index.php';
    }
}

?>