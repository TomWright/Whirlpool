<?php

namespace Whirlpool;

abstract class Request
{

    protected static $uri = '';
    protected static $requestString = '';
    protected static $subdomain = null;

    public static function init()
    {
    }


    protected static function getVar($id, array &$array, $default = null)
    {
        $val = array_key_exists($id, $array) ? $array[$id] : $default;
        return $val;
    }


    public static function uri()
    {
        if (strlen(static::$uri) === 0) {
            static::$uri = static::getVar('REQUEST_URI', $_SERVER, '');
        }
        return static::$uri;
    }


    public static function subdomain()
    {
        if (static::$subdomain === null) {
            $serverName = explode('.', $_SERVER['SERVER_NAME']);
            if (count($serverName) > 2) {
                $ignoredSubdomains = Config::get('general.ignoredSubdomains');
                $subdomain = array_shift($serverName);
                if (!in_array($subdomain, $ignoredSubdomains)) {
                    static::$subdomain = $subdomain;
                }
            }
        }
        return static::$subdomain;
    }

}