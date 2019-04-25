<?php

namespace App\Http\Requests\Thread;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class ThreadLockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth() &&
            (Bouncer::can('moderate-channels') ||
                Bouncer::can('moderate-channels', request()->route('channel')));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
