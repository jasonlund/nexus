<?php
namespace App\Services;

use Purifier;

class PurifyService
{
    public static function clean($string)
    {
        return Purifier::clean($string);
    }

    public static function simple($string)
    {
        $config = ['HTML.Allowed' => 'strong,em,s,u,p'];

        return Purifier::clean($string, $config);
    }

    public static function strip($string)
    {
        $config = ['HTML.Allowed' => ''];

        return Purifier::clean($string, $config);
    }
}
