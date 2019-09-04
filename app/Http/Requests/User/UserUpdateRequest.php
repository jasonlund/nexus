<?php

namespace App\Http\Requests\User;

use App\Services\UsersService;
use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && Bouncer::can('update-users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return UsersService::validationRules('update');
    }
}
