<?php

namespace Whirlpool;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Whirlpool\Router\Router;

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
     * Initialize Whirlpool
     */
    public function __construct()
    {
        $this->init();
    }


    /**
     * @throws \Exception
     */
    protected function init()
    {
        $this->setEnvironment();

        // Load class aliases
        $aliases = Config::get('aliases');
        foreach ($aliases as $orig => $new) {
            class_alias($orig, $new, true);
        }

        static::$container = new Container();

        Session::init();
        Request::init();

        $this->initRouter();

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


    protected function initRouter()
    {
        $this->router = new Router();
        $this->router->setHttpMethod($_SERVER['REQUEST_METHOD']);
        $this->router->setUri(rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    }


    public function run()
    {
        EventHandler::triggerEvent('whirlpool-load-action', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $this->loadAction();
        EventHandler::triggerEvent('whirlpool-loaded-action', $this->action);
        EventHandler::triggerEvent('whirlpool-execute-action', $this->action);
        $response = $this->executeAction();
        EventHandler::triggerEvent('whirlpool-executed-action', $response);
        Session::cleanUp();
    }


    protected function loadAction()
    {
        $controller = $this->router->getController();
        $action = $this->router->getMethod();
        $params = $this->router->getVars();

        if ($controller === null) {
            $controller = Config::get('routing.notFoundController');
            $action = Config::get('routing.notFoundAction');
        }

        if ($action === null) {
            $action = Config::get('routing.defaultAction');
        }

        $this->setAction($controller, $action, $params);

        $notFound = false;

        if (! class_exists($this->action->controller)) {
            $notFound = true;
        } else {
            $controller = $this->make($this->action->controller);
            if (! method_exists($controller, $this->action->action)) {
                $notFound = true;
            }
        }

        if ($notFound) {
            // The controller or action does not exist.
            $controller = Config::get('routing.notFoundController');
            $action = Config::get('routing.notFoundAction');

            $this->setAction($controller, $action, $params);
        }
    }


    /**
     * @param $controller
     * @param $action
     * @param $params
     */
    protected function setAction($controller, $action, $params)
    {
        $action .= 'Action';

        $this->action = new \stdClass();
        $this->action->controller = $controller;
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
     * @param $className
     * @param bool $singleton
     * @return object
     */
    public static function make($className, $singleton = true)
    {
        return static::$container->make($className, $singleton);
    }


    /**
     * Set the ENVIRONMENT constant.
     */
    private function setEnvironment()
    {
        $environment = 'production';

        if (isset($_SERVER['ENVIRONMENT']) && $_SERVER['ENVIRONMENT'] == 'development' || isset($_SERVER['DEVELOPMENT']) && $_SERVER['DEVELOPMENT'] == 1) {
            $environment = 'development';
        }

        define('ENVIRONMENT', $environment);

        switch (ENVIRONMENT) {
            case 'development':
                ini_set('display_errors', 1);
                error_reporting(-1);
                break;
        }
    }

}