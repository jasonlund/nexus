<?php

namespace App\Http\Requests\User;

use App\Services\UsersService;
use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class UserBanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (auth()->check() && Bouncer::can('ban-users')) &&
            (Bouncer::is(request()->route('user'))->notA('admin', 'super-moderator', 'moderator'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return UsersService::validationRules('ban');
    }
}
