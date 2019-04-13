<?php

namespace App\Http\Requests\Reply;

use App\Services\RepliesService;
use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Rules\RichTextRequired;

class ReplyUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth() &&
            (request()->route('reply')->user_id == auth()->user()->id ||
                Bouncer::can('moderate-channels') ||
                Bouncer::can('moderate-channels', request()->route('channel')));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return RepliesService::validationRules('update');
    }
}
