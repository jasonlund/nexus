<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UserSelfUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = User::validationRules();
        array_unshift($rules['password'], 'nullable');

        return $rules;
    }
}
