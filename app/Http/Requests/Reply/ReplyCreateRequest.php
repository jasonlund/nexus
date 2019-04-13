<?php

namespace App\Http\Requests\Reply;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\RepliesService;

class ReplyCreateRequest extends FormRequest
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
        return RepliesService::validationRules('create');
    }
}
