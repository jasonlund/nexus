<?php

namespace App\Http\Controllers;

use App\Http\Requests\Image\ImageCreateRequest;
use App\Services\ImagesService;
use Illuminate\Http\Request;
use Storage;

class ImagesController extends Controller
{
    protected $service;

    public function __construct(ImagesService $service)
    {
        $this->service = $service;
    }

    public function store(ImageCreateRequest $request)
    {
        $path = $this->service->create(request());

        return response()->json([
            'success' => true,
            'url' => url(Storage::url($path))
        ]);
    }
}
