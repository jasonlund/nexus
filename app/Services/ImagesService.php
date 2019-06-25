<?php
namespace App\Services;

class ImagesService
{
    public static function validationRules($action = null)
    {
        $rules = collect([
            'file' => ['required', 'image', 'max:1000']
        ]);

        switch ($action) {
            case "create":
                $rules = $rules->only(['file']);
                break;
        }

        return $rules->toArray();
    }

    public function create($data)
    {
        $file_path = $data->file('file')->store('images', 'public');

        return $file_path;
    }
}
