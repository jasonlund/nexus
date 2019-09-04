<?php

namespace App\Http\Requests\ChannelCategory;

use Illuminate\Foundation\Http\FormRequest;
use Bouncer;

class ChannelCategoryDestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && Bouncer::can('delete-channels');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
