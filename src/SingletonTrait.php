<?php

namespace Whirlpool;

trait SingletonTrait
{

    /**
     * @var self
     */
    private static $instance;


    /**
     * SingletonTrait constructor.
     */
    private function __construct()
    {
    }


    /**
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}