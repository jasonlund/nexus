<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Embed\Embed;
use Illuminate\Validation\ValidationException;

class EmbedController extends Controller
{
    public function show(Request $request)
    {
        if($request->has('url') !== true) $this->validationException();

        $info = Embed::create(request('url'))->code;

        if($info === null) $this->validationException();

        return $info;
    }

    private function validationException()
    {
        throw ValidationException::withMessages([
            'url' => 'Invalid source url provided.'
        ]);
    }
}
