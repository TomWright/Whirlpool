<?php

namespace Whirlpool;

class Config
{

    /**
     * An array containing all of the cached config items.
     * @var array
     */
    protected static $config = [];


    /**
     * Return a config item.
     * @param $configName
     * @return mixed|null
     */

    public static function get($configName)
    {
        if (!is_array($configName)) $configName = explode('.', $configName);

        if (count($configName) === 0) return null;

        $configVal = static::loadFromCache($configName);

        if ($configVal === null) {
            $configVal = static::loadFromFile($configName);
        }

        return $configVal;
    }


    /**
     * Load and return a config item from the cache.
     * @param array $configName
     * @return mixed|null
     */

    protected static function loadFromCache(array $configName)
    {
        $configVal = static::$config;

        foreach ($configName as $name) {
            if (array_key_exists($name, $configVal)) {
                $configVal = $configVal[$name];
            } else {
                $configVal = null;
                break;
            }
        }

        return $configVal;
    }


    /**
     * Load a config from a file, cache it and return it.
     * @param array $configName
     * @return mixed|null
     */

    protected static function loadFromFile(array $configName)
    {
        $configFile = $configName[0];
        $filePath = APP_PATH . "config/{$configFile}.php";

        if (!is_file($filePath)) {
            return null;
        }

        $configVal = require $filePath;

        static::$config[$configFile] = $configVal;

        return static::loadFromCache($configName);
    }

}