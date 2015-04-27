<?php

namespace Whirlpool;

class Container
{

    /**
     * An array of objects stored in the container
     * $className => $object
     * @var array
     */
    protected $objects = array();

    /**
     * An array of class aliases
     * Useful for binding certain interfaces to a class type within the container
     * @var array
     */
    protected $aliases = array();


    /**
     * @param string $from
     * @param string $to
     */
    public function bind($from, $to)
    {
        if (is_object($from)) {
            $from = get_class($from);
        }

        if (is_object($to)) {
            $to = get_class($to);
        }

        $this->aliases[$from] = $to;
    }


    /**
     * @param string $className
     * @param bool $singleton
     * @return object
     */
    public function make($className, $singleton = true)
    {
        if (array_key_exists($className, $this->aliases)) {
            $className = $this->aliases[$className];
        }

        if ($singleton === true && array_key_exists($className, $this->objects)) {
            return $this->objects[$className];
        }

        $args = array();

        $class = new \ReflectionClass($className);
        $constructor = $class->getConstructor();
        if ($constructor !== null) {
            $params = $constructor->getParameters();
            if (is_array($params)) {
                foreach ($params as $param) {
                    $parameterClass = $param->getClass()->getName();
                    $parameterObject = $this->make($parameterClass, true);
                    $args[] = $parameterObject;
                }
            }
        }

        $object = $class->newInstanceArgs($args);

        if ($singleton) {
            $this->aliases[$className] = $object;
        }

        return $object;
    }


    public function add($object)
    {
        if (! is_object($object)) {
            throw new \Exception("Parameter must be an instance of an object.");
        }

        $className = get_class($object);
        $this->objects[$className] = $object;
    }

}