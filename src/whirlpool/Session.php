<?php

namespace Whirlpool;

class Session
{

    protected static $flashKeys = [];


    public static function init()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        static::$flashKeys = static::get('_flashKeys');
    }


    public static function get($key, $default = null)
    {
        $value = $default;
        if (isset($_SESSION[$key])) $value = $_SESSION[$key];
        return $value;
    }


    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }


    public static function flash($key, $value)
    {
        static::set($key, $value);
        static::$flashKeys[] = $key;
        static::set('_flashKeys', static::$flashKeys);
    }


    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }


    public static function clearFlashMessages()
    {
        foreach (static::$flashKeys as $key) {
            static::remove($key);
        }
    }

}