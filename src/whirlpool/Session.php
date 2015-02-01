<?php

namespace Whirlpool;

class Session
{

    protected static $flashKeys = array();


    public static function init()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $_SESSION['security']['userAgent'] = sha1(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'nothing');
        }

        if (! static::doChecks()) {
            static::destroy();
            static::init();
            return;
        }

        static::$flashKeys = static::get('_flashKeys', []);
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


    public static function destroy()
    {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
        session_destroy();
    }


    protected static function doChecks()
    {
        $validUserAgent = isset($_SESSION['security']['userAgent']) && $_SESSION['security']['userAgent'] == sha1(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'nothing');
        return $validUserAgent;
    }

}