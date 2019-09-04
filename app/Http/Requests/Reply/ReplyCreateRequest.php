<?php

namespace App\Http\Requests\Reply;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\RepliesService;
use Bouncer;

class ReplyCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && (!request()->route('thread')->locked
                || Bouncer::can('moderate-channels')
                || Bouncer::can('moderate-channels', request()->route('channel')));
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
