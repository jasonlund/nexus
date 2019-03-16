<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends BaseModel
{
    use SoftDeletes;

    /**
     * Cast Soft Deletes timestamp as a date.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
