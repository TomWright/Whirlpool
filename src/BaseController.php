<?php

namespace Whirlpool;

abstract class BaseController
{

    protected $twig = null;

    public function __construct()
    {
        $this->init();
    }


    protected function init()
    {
        $viewPath = Config::get('general.viewPath');
        $twigLoader = new \Twig_Loader_Filesystem($viewPath);
        $this->twig = new \Twig_Environment($twigLoader, [
                'cache' => APP_DATA . '/cache/twig',
                'auto_reload' => true,
            ]);
    }


    protected function loadView($view, array $data = array())
    {
        $response = $this->twig->render($view . '.php', $data);
        return $response;
    }


    protected function displayView($view, array $data = array())
    {
        echo $this->loadView($view, $data);
    }

}