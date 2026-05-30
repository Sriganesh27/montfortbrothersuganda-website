<?php

require_once __DIR__ . '/../../../models/api/students/FetchPhotoEditModel.php';

class FetchPhotoEditController
{
    private $model;

    public function __construct()
    {
        $this->model = new FetchPhotoEditModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/fetch_photo_edit/index.php';
    }
}

?>