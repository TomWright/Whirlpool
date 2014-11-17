<?php

use Whirlpool\BaseController;

class HomeController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }


    public function indexAction($name, $age)
    {
       $this->displayView('test', ['name' => $name, 'age' => $age]);
    }

    public function passwordTestAction()
    {
        $name = Input::get('name', 'Jack');
        echo $name;
    }

}