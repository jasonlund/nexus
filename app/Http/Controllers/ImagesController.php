<?php

namespace App\Http\Controllers;

use App\Http\Requests\Image\ImageCreateRequest;
use App\Services\ImagesService;
use Storage;

class ImagesController extends Controller
{
    /**
     * The Image Service.
     *
     * @var ImagesService
     */
//    protected $service;

    /**
     * ImagesController constructor.
     *
     * @param ImagesService $service
     */
    public function __construct(ImagesService $service)
    {
        parent::__construct();
//        $this->service = $service;
    }

    /**
     * Store the given image and return it's url.
     *
     * @param ImageCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ImageCreateRequest $request)
    {
        $path = $this->service->create(request());

        return response()->json([
            'success' => true,
            'url' => url(Storage::url($path))
        ]);
    }
}
