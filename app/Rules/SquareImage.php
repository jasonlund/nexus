<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SquareImage implements Rule
{
    /**
     * Determine if the width and height dimensions of an image are exactly
     * equal.
     *
     * @param   string  $attribute
     * @param   mixed   $value
     *
     * @return  boolean
     */
    public function passes($attribute, $value)
    {
        try {
            $size = getimagesize($value);
        } catch (\Exception $e) {
            return false;
        }

        return $size[0] === $size[1];
    }

    /**
     * Get the validation error message.
     *
     * @return  string
     */
    public function message()
    {
        return 'The :attribute must be square.';
    }
}
