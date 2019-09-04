<?php

namespace App\Services;

use Purifier;

class PurifyService
{
    /**
     * Purify the given string with the default configuration.
     *
     * @param   string  $string
     *
     * @return  string
     */
    public static function clean($string)
    {
        return Purifier::clean($string);
    }

    /**
     * Purify the given string with a simple configuration.
     *
     * @param   string  $string
     *
     * @return  string
     */
    public static function simple($string)
    {
        $config = ['HTML.Allowed' => 'strong,em,s,u,p'];

        return Purifier::clean($string, $config);
    }

    /**
     * Purify the given string stripping all HTML.
     *
     * @param   string  $string
     *
     * @return  string
     */
    public static function strip($string)
    {
        $config = ['HTML.Allowed' => ''];

        return Purifier::clean($string, $config);
    }
}
