<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RichTextRequired implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $value = preg_replace('/\s/', '', $value);
        $value = preg_replace('~\x{00a0}~','', $value);
        $value = strip_tags($value);

        if($value === '') return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is required.';
    }
}
