<?php

namespace App\Services;

use App\Models\Emote;
use Illuminate\Validation\Rule;
use App\Rules\SquareImage;

class EmotesService
{
    /**
     * Get the request validation rules given an optional action.
     *
     * @param   string|null  $action
     *
     * @return  array
     */
    public static function validationRules($action = null)
    {
        $rules = collect([
            'name' => [
                'bail', 'required', 'alpha', 'max:30', 'unique:emotes,name'
            ],
            'file' => [
                'bail', 'required', 'file', 'mimes:png,gif', 'max:256',
                Rule::dimensions()->maxWidth(128)->maxHeight(128)->minWidth(32)->minHeight(32), new SquareImage
            ]
        ]);

        switch ($action) {
            case "create":
                $rules = $rules->only(['name', 'file']);
                break;
        }

        return $rules->toArray();
    }

    /**
     * Create an Emote from the given data.
     *
     * @param   array  $data
     *
     * @return  Emote
     */
    public function create($data)
    {
        $fileName = $data['name'] . '.' . $data->file('file')->extension();
        $path = $data->file('file')
            ->storeAs('emotes', $fileName, 's3');

        return Emote::create([
            'name' => $data['name'],
            'path' => $path,
            'user_id' => auth()->user()->id
        ]);
    }
}
