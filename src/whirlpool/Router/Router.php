<?php

namespace Whirlpool\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Whirlpool\Config;

class Router
{

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @var string
     */
    protected $uri;

    /**
     * NULL if the dispatcher hasn't been dispatched yet.
     * @var null|array
     */
    protected $dispatcherResult = null;

    /**
     * The handler that should be used if a page cannot be found.
     * @var null
     */
    protected $notFoundHandler = null;


    /**
     * Router constructor.
     * Include the route files and set the dispatcher object.
     */
    public function __construct()
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            $routeFiles = Config::get('routing.routeFiles');
            if (is_array($routeFiles)) {
                foreach ($routeFiles as $file) {
                    $this->importRoutesFile($file, $r);
                }
            }
        });
    }


    /**
     * Set the HTTP Method to be used by the router.
     * @param $method
     */
    public function setHttpMethod($method)
    {
        $this->httpMethod = $method;
    }


    /**
     * Set the URI to be used by the router.
     * @param $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }


    /**
     * Set the handler that should be used when a page/route cannot be found.
     * @param string $handler
     */
    public function setNotFoundHandler($handler)
    {
        $this->notFoundHandler = $handler;
    }


    /**
     * Import a PHP file which references $router.
     * @param string $file
     * @param $r
     */
    protected function importRoutesFile($file, $r)
    {
        if (is_file($file)) {
            require_once $file;
        }
    }


    /**
     * Dispatches/Processes the routes.
     */
    public function dispatch()
    {
        $routeInfo = $this->dispatcher->dispatch($this->httpMethod, $this->uri);

        $this->dispatcherResult = array(
            'controller' => null,
            'method' => null,
            'vars' => array(),
        );

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // Use the not found handler.
                $atPosition = strpos($this->notFoundHandler, '@');
                if (is_string($atPosition) && $atPosition !== false) {
                    $this->dispatcherResult['controller'] = substr($this->notFoundHandler, 0, $atPosition);
                    $this->dispatcherResult['method'] = substr($this->notFoundHandler, $atPosition + 1);
                }
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                // Use the not found handler.
                $atPosition = strpos($this->notFoundHandler, '@');
                if (is_string($atPosition) && $atPosition !== false) {
                    $this->dispatcherResult['controller'] = substr($this->notFoundHandler, 0, $atPosition);
                    $this->dispatcherResult['method'] = substr($this->notFoundHandler, $atPosition + 1);
                }
                break;

            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $atPosition = strpos($handler, '@');
                if (is_string($atPosition) && $atPosition !== false) {
                    // There is an @. This implies we have a Controller@Method to look for.
                    $this->dispatcherResult['controller'] = substr($handler, 0, $atPosition);
                    $this->dispatcherResult['method'] = substr($handler, $atPosition + 1);
                }
                // TODO Implement functionality to deal with callable's etc.

                if (is_array($vars)) {
                    $this->dispatcherResult['vars'] = $vars;
                }
                break;
        }
    }


    /**
     * Fetches the specified result from the dispatcher.
     * @param $type
     * @return null
     */
    public function getDispatcherResult($type)
    {
        // Ensure that the routes have been processed.
        if ($this->dispatcherResult === null) {
            $this->dispatch();
        }

        $result = null;

        if (array_key_exists($type, $this->dispatcherResult)) {
            $result = $this->dispatcherResult[$type];
        }

        return $result;
    }


    /**
     * Fetches the controller from the dispatcher result.
     * @return null
     */
    public function getController()
    {
        return $this->getDispatcherResult('controller');
    }


    /**
     * Fetches the method from the dispatcher result.
     * @return null
     */
    public function getMethod()
    {
        return $this->getDispatcherResult('method');
    }


    /**
     * Fetches the vars from the dispatcher result.
     * @return null
     */
    public function getVars()
    {
        return $this->getDispatcherResult('vars');
    }

}