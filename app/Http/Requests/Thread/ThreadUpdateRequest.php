<?php

namespace App\Http\Requests\Thread;

use App\Services\ThreadsService;
use Illuminate\Foundation\Http\FormRequest;
use Bouncer;
use App\Rules\RichTextRequired;

class ThreadUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth() &&
            (request()->route('thread')->user_id == auth()->user()->id ||
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
        return ThreadsService::validationRules('update');
    }
}
