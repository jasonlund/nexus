<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * The corresponding service.
     *
     * @var mixed|null
     */
    public $service;

    /**
     * Controller constructor.
     * Retreive and attach the applicable Service if it exists.
     *
     * @param   mixed   $model
     *
     * @return  void
     */
    public function __construct($model = null)
    {
        if (!$model) {
            $class = explode('\\', get_class($this));
            $class = $class[count($class) - 1];
            $service = str_replace('Controller', 'Service', $class);
        } else {
            $service = Str::plural($model) . 'Service';
        }

        if (class_exists('\\App\\Services\\' . $service)) {
            $service = '\\App\\Services\\' . $service;
            $this->service = new $service();
        }
    }
}
