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
    public $router = null;

    /**
     * @var \stdClass|null
     */
    protected $action = null;

    /**
     * @var Capsule
     */
    protected $capsule = null;

    /**
     * @var Container
     */
    public static $container = null;


    /**
     * Set up the autoloading and then initialize Whirlpool
     */
    public function __construct()
    {
        spl_autoload_register([$this, 'autoload']);
        $this->init();
    }


    /**
     * @throws \Exception
     */
    protected function init()
    {
        // Load class aliases
        $aliases = Config::get('aliases');
        foreach ($aliases as $orig => $new) {
            class_alias($orig, $new, true);
        }

        static::$container = new Container();

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

        $databaseConfig = Config::get('database');
        if ($databaseConfig !== null && is_array($databaseConfig)) {
            $this->capsule = new Capsule();
            foreach ($databaseConfig as $name => $conf) {
                if (array_key_exists('name', $conf) && strlen($conf['name']) > 0) {
                    $name = $conf['name'];
                    unset($conf['name']);
                }
                $this->capsule->addConnection($conf, $name);
            }
            $this->capsule->bootEloquent();
        }

        $hookConfig = Config::get('hooks');
        if (is_array($hookConfig)) {
            foreach ($hookConfig as $event => $callable) {
                EventHandler::addListener($event, $callable);
            }
        }

        EventHandler::triggerEvent('whirlpool-initialized', $this);
    }


    public function run()
    {
        EventHandler::triggerEvent('whirlpool-load-action', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $this->loadAction();
        EventHandler::triggerEvent('whirlpool-loaded-action', $this->action);
        EventHandler::triggerEvent('whirlpool-execute-action', $this->action);
        $response = $this->executeAction();
        EventHandler::triggerEvent('whirlpool-executed-action', $response);
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
            if (isset($route->extraTokens) && is_array($route->extraTokens)) {
                $params = array_merge($route->extraTokens, $params);
            }
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


    /**
     * @return mixed
     */
    protected function executeAction()
    {
        $controller = $this->make($this->action->controller);

        EventHandler::triggerEvent('whirlpool-controller-initialized', $controller, $this->action);

        $response = call_user_func_array([$controller, $this->action->action], $this->action->params);

        return $response;
    }


    /**
     * @param $class
     */
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

        $configDirectories = Config::get('autoload.directories');
        if (is_array($configDirectories)) $directories = array_merge($directories, $configDirectories);
        if (is_array($configDirectories)) {
            foreach ($configDirectories as $key => $val) {
                $configDirectories[$key] = APP_PATH . "/{$val}/";
            }
            $directories = array_merge($directories, $configDirectories);
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
            $configSubdomainDirectories = Config::get('autoload.subdomainDirectories');
            if (is_array($configSubdomainDirectories)) {
                foreach ($configSubdomainDirectories as $key => $val) {
                    $configSubdomainDirectories[$key] = "/subdomains/{$subdomain}/{$val}/";
                }
                $subdomainDirectories = array_merge($subdomainDirectories, $configSubdomainDirectories);
            }
            $directories = array_merge($subdomainDirectories, $directories);
        }

        $found = false;
        foreach ($directories as $dir) {
            $path = "{$dir}/{$class}.php";
            if (is_file($path)) {
                $found = true;
                require_once $path;
                break;
            }
        }

        if ($found === false) {
            EventHandler::triggerEvent('whirlpool-class-not-found', $class);
        }
    }


    /**
     * @param $className
     * @param bool $singleton
     * @return object
     */
    public static function make($className, $singleton = true)
    {
        return static::$container->make($className, $singleton);
    }

}