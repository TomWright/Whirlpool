<?php

namespace Whirlpool;

class EventHandler
{

    /**
     * An array containing all of the registered event listeners.
     * @var array
     */
    protected static $listeners = [];


    /**
     * Register a new event listener
     * @param string $event The name of the event to listen for
     * @param callable $action The callable action that should be executed
     * @throws \Exception
     * @return bool
     */
    public static function addListener($event, $action)
    {
        if (! is_callable($action)) {
            throw new \Exception("Event Listener required the action to be callable.");
            return false;
        }

        if (! array_key_exists($event, static::$listeners)) {
            static::$listeners[$event] = array();
        }

        static::$listeners[$event][] = $action;

        return true;
    }


    /**
     * @param string $event The name of the event to trigger
     */
    public static function triggerEvent($event)
    {
        $args = func_get_args();
        array_shift($args);
        if (array_key_exists($event, static::$listeners)) {
            foreach (static::$listeners[$event] as $listener) {
                call_user_func_array($listener, $args);
            }
        }
    }

}