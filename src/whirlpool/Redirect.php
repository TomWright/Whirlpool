<?php

namespace Whirlpool;

class Redirect
{

    public static function to($location)
    {
        header("Location: {$location}");
        die;
    }

}