<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/ProfileDataViewModel.php';

class ProfileDataViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new ProfileDataViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/profile_data_view/index.php';
    }
}

?>