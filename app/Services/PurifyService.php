<?php
namespace App\Services;

use Purifier;

class PurifyService
{
    public static function clean($string)
    {
        // $config = ['HTML.Allowed' => 'h3,strong,em,s,u,a[href],ul,ol,li,p,blockquote,img[src],div,iframe'];

        // return Purify::clean($string, $config);

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
