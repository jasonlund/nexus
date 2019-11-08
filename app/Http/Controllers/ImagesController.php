<?php

namespace App\Http\Controllers;

use App\Http\Requests\Image\ImageCreateRequest;
use Storage;

class ImagesController extends Controller
{
    /**
     * ImagesController constructor.
     */
    /**
     * ImagesController constructor.
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Store the given image and return it's url.
     *
     * @param   ImageCreateRequest  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function store(ImageCreateRequest $request)
    {
        $path = $this->service->create($request);

        return response()->json([
            'success' => true,
            'url' => Storage::url($path)
        ]);
    }
}
