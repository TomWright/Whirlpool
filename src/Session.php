<?php

namespace Whirlpool;

class Session
{

    /**
     * The keys/names of the flash data that was set in the previous request.
     * @var array
     */
    protected static $oldFlashKeys = array();

    /**
     * The keys/names of the flash data that was set in the current request.
     * @var array
     */
    protected static $flashKeys = array();


    /**
     * Initialise the Session and ensure that a session is created.
     */
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

        // Grab the flash keys from the session and store them.
        static::$oldFlashKeys = static::get('_flashKeys', []);
    }


    /**
     * Returns the value of $key in the session. If $key isn't found, $default is returned.
     * @param $key
     * @param null $default
     * @return null
     */
    public static function get($key, $default = null)
    {
        $value = $default;
        if (isset($_SESSION[$key])) $value = $_SESSION[$key];
        return $value;
    }


    /**
     * Set a value in the session.
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }


    /**
     * Sets a value in the session, that should only be available in the next request.
     * @param $key
     * @param $value
     */
    public static function flash($key, $value)
    {
        static::set($key, $value);
        static::addFlashKey($key);
    }


    /**
     * Adds the specified $key to the $flashKeys.
     * It also removes $key from $oldFlashKeys if it exists.
     * @param $key
     */
    protected static function addFlashKey($key)
    {
        if (! in_array($key, static::$flashKeys)) {
            static::$flashKeys[] = $key;
            static::set('_flashKeys', static::$flashKeys);
        }
        $keyIndex = array_search($key, static::$oldFlashKeys);
        if ($keyIndex !== false) {
            unset(static::$oldFlashKeys[$keyIndex]);
        }
    }


    /**
     * Removes $key from the session, and removes it from the $flashKeys arrays.
     * @param $key
     */
    public static function remove($key)
    {
        unset($_SESSION[$key]);
        $keyIndex = array_search($key, static::$flashKeys);
        if ($keyIndex !== false) {
            unset(static::$flashKeys[$keyIndex]);
            static::set('_flashKeys', static::$flashKeys);
        }
        $keyIndex = array_search($key, static::$oldFlashKeys);
        if ($keyIndex !== false) {
            unset(static::$oldFlashKeys[$keyIndex]);
        }
    }


    /**
     * "Cleans up" the sessions.
     * Basically, this should be ran at the end of the request.
     */
    public static function cleanUp()
    {
        // We want to clear out all "old" flash messages.
        static::clearFlashMessages(true, false);
    }


    /**
     * Clears flash messages using the $flashKeys arrays to know which session variables to clear.
     * @param bool $clearOld
     * @param bool $clearCurrent
     */
    public static function clearFlashMessages($clearOld = true, $clearCurrent = false)
    {
        if ($clearOld) {
            foreach (static::$oldFlashKeys as $key) {
                static::remove($key);
            }
        }
        if ($clearCurrent) {
            foreach (static::$flashKeys as $key) {
                static::remove($key);
            }
        }
    }


    /**
     * Destroy the session.
     */
    public static function destroy()
    {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
        session_destroy();
    }


    /**
     * Check that our session "security checks" are satisfied.
     * @return bool
     */
    protected static function doChecks()
    {
        $validUserAgent = isset($_SESSION['security']['userAgent']) && $_SESSION['security']['userAgent'] == sha1(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'nothing');
        return $validUserAgent;
    }

}