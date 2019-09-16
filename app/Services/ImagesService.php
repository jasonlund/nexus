<?php

namespace App\Services;

class ImagesService
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
            'file' => ['bail', 'required', 'image', 'max:1024']
        ]);

        switch ($action) {
            case "create":
                $rules = $rules->only(['file']);
                break;
        }

        return $rules->toArray();
    }

    /**
     * Store the image in the given request and return it's file path.
     *
     * @param   App\Http\Requests\Image\ImageCreateRequest  $data
     *
     * @return  string
     */
    public function create($data)
    {
        return $data->file('file')->store('images');
    }
}
