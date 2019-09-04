<?php

namespace App\Http\Requests\Self;

use App\Services\UsersService;
use Illuminate\Foundation\Http\FormRequest;

class SelfUpdateRequest extends FormRequest
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
        return UsersService::validationRules('update.self');
    }
}
