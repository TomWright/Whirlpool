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
        $viewPath = APP_PATH . '/views';
        $subdomain = Request::subdomain();
        if ($subdomain !== null) {
            $viewPath = APP_PATH . "/subdomains/{$subdomain}/views";
        }
        $twigLoader = new \Twig_Loader_Filesystem($viewPath);
        $this->twig = new \Twig_Environment($twigLoader, [
                'cache' => APP_PATH . '/data/cache/twig',
                'auto_reload' => true,
            ]);
    }


    protected function displayView($view, array $data = array())
    {
        echo $this->twig->render($view . '.php', $data);
    }

}