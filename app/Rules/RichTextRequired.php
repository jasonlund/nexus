<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RichTextRequired implements Rule
{
    /**
     * Strip all HTML and check if the string has length.
     *
     * @param   string  $attribute
     * @param   mixed   $value
     *
     * @return  boolean
     */
    public function passes($attribute, $value)
    {
        $value = strip_html_whitespace($value);

        if ($value === '') return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return  string
     */
    public function message()
    {
        return 'The :attribute is required.';
    }
}
