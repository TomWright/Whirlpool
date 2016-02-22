<?php

namespace Whirlpool;

class Input
{

    public static function get($key, $default = null)
    {
        $result = $default;
        if (isset($_GET[$key])) $result = $_GET[$key];
        return $result;
    }


    public static function post($key, $default = null)
    {
        $result = $default;
        if (isset($_POST[$key])) $result = $_POST[$key];
        return $result;
    }


    public static function any($key, $default = null)
    {
        $result = $default;
        if (isset($_REQUEST[$key])) $result = $_REQUEST[$key];
        return $result;
    }

}