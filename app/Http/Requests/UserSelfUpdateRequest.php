<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'min:3',
                'max:16',
                'regex:/^^[a-zA-Z0-9_]+((\.(-\.)*-?|-(\.-)*\.?)[a-zA-Z0-9_]+)*$/i',
                                        // alphanumeric, hyphens, underscores and periods.
                Rule::unique('users')->ignore(auth()->user()->username, 'username')
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore(auth()->user()->email, 'email')
            ],
        ];
    }
}
