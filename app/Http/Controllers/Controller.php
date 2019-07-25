<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $service;

    public function __construct()
    {
        $class = explode('\\', get_class($this));
        $class = $class[count($class) - 1];
        $service = str_replace('Controller', 'Service', $class);
        if(class_exists('\\App\\Services\\' . $service)){
            $service = '\\App\\Services\\' . $service;
            $this->service = new $service();
        }
    }
}
