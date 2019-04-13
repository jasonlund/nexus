<?php

namespace App\Http\Requests\Channel;

use App\Services\ChannelsService;
use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class ChannelUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && Bouncer::can('update-channels');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return ChannelsService::validationRules('update');
    }
}
