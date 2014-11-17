<?php

namespace Whirlpool;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Aura\Router\RouterFactory;
use \Aura\Router\Router;

class Whirlpool
{

    /**
     * @var Router;
     */
    protected $router = null;

    protected $action = null;


    public function __construct()
    {
        spl_autoload_register([$this, 'autoload']);
        $this->init();
    }


    protected function init()
    {
        // Load class aliases
        $aliases = Config::get('aliases');
        foreach ($aliases as $orig => $new) {
            class_alias($orig, $new, true);
        }

        Session::init();
        Request::init();

        $routerFactory = new RouterFactory();
        $this->router = $routerFactory->newInstance();

        $routeFiles = Config::get('routing.routeFiles');
        if (is_array($routeFiles)) {
            function initRoutes(Router & $router, $file) {
                if (is_file($file)) {
                    require_once $file;
                }
            }
            foreach ($routeFiles as $file) {
                initRoutes($this->router, $file);
            }
        }

        $capsule = new Capsule();

        $capsule->addConnection(
            Config::get('database')
        );

        $capsule->bootEloquent();
    }


    public function run()
    {
        $this->loadAction();
        $this->executeAction();
        Session::clearFlashMessages();
    }


    protected function loadAction()
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $route = $this->router->match($path, $_SERVER);

        $params = [];
        $controller = null;
        $action = null;

        if ($route !== false) {
            $params = $route->params;
            if (isset($params['controller'])) {
                $controller = $route->params['controller'];
                $action = ((isset($params['method']) && strlen($params['method'])) ? $params['method'] : Config::get('routing.defaultAction'));
            }
            unset($params['controller'], $params['method'], $params['action']);
        }

        if ($controller === null) {
            $controller = Config::get('routing.notFoundController');
            $action = Config::get('routing.notFoundAction');
        }

        if ($action === null) {
            $action = Config::get('routing.defaultAction');
        }

        $controllerTemp = explode('/', $controller);
        $controller = ucfirst(array_pop($controllerTemp));
        $controllerDir = implode('/', $controllerTemp);

        $controller .= 'Controller';
        $action .= 'Action';

        $this->action = new \stdClass();
        $this->action->controller = $controller;
        $this->action->controllerDir = $controllerDir;
        $this->action->action = $action;
        $this->action->params = $params;
    }


    protected function executeAction()
    {
        $controller = new $this->action->controller();

        $response = call_user_func_array([$controller, $this->action->action], $this->action->params);

        if ($response !== null) {
            var_dump($response);
        }
    }


    public function autoload($class)
    {

        $directories = [
            APP_PATH . '/controllers',
            APP_PATH . '/models',
        ];

        if (isset($this->action->controllerDir) && strlen($this->action->controllerDir)) {
            $controllerDirPath = APP_PATH . "/controllers/{$this->action->controllerDir}";
            array_unshift($directories, $controllerDirPath);
        }

        $subdomain = Request::subdomain();

        if ($subdomain !== null) {
            $subdomainDirectories = [
                APP_PATH . "/subdomains/{$subdomain}/controllers",
                APP_PATH . "/subdomains/{$subdomain}/models",
            ];
            if (isset($this->action->controllerDir) && strlen($this->action->controllerDir)) {
                array_unshift($subdomainDirectories, APP_PATH . "/subdomains/{$subdomain}/controllers/{$this->action->controllerDir}");
            }
            $directories = array_merge($subdomainDirectories, $directories);
        }

        foreach ($directories as $dir) {
            $path = "{$dir}/{$class}.php";
            if (is_file($path)) {
                require_once $path;
                break;
            }
        }
    }

}