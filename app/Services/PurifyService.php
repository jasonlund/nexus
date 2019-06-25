<?php
namespace App\Services;

use Purify;

class PurifyService
{
    public static function clean($string)
    {
        return Purify::clean($string);
    }

    public static function simple($string)
    {
        $config = ['HTML.Allowed' => 'strong,em,s,u,p'];

        return Purify::clean($string, $config);
    }
}
