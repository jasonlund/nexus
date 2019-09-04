<?php

namespace App\Http\Requests\Emote;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Services\EmotesService;

class EmoteCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && Bouncer::can('create-emotes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return EmotesService::validationRules('create');
    }
}
