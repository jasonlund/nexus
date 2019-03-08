<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueCaseInsensitive implements Rule
{
    protected $model;
    protected $ignore;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($model, $ignore)
    {
        $this->model = $model;
        $this->ignore = $ignore;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(strtolower($value) === strtolower($this->ignore)) return true;
        return $this->model::whereRaw('lower(' . $attribute . ') like (?)',["%{$value}%"])
                ->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The username must be unique.';
    }
}
