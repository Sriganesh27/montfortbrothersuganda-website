<?php

require_once __DIR__ . '/../../../models/api/students/UploadTempModel.php';

class UploadTempController
{
    private $model;

    public function __construct()
    {
        $this->model = new UploadTempModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/upload_temp/index.php';
    }
}

?>