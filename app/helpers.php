<?php

if (! function_exists('item_response')) {
    function item_response($item, $transformer, $includes = []) {
        $transformer = "\\App\\Transformers\\{$transformer}";

        return response()->json(fractal()
            ->item($item)
            ->parseIncludes($includes)
            ->transformWith(new $transformer()));
    }
}

if (! function_exists('collection_response')) {
    function collection_response($data, $transformer, $includes = []) {
        $data = $data->get();
        $transformer = "\\App\\Transformers\\{$transformer}";

        return response()->json(fractal()
            ->collection($data)
            ->parseIncludes($includes)
            ->transformWith(new $transformer()));
    }
}

if (! function_exists('paginated_response')) {
    /**
     * Paginate a query, transform it with the defined Transformer and return it as a JSON response.
     *
     * @param $data
     * @param $transformer
     * @param array $includes
     * @return \Illuminate\Http\JsonResponse
     */
    function paginated_response($data, $transformer, $includes = []) {
        $limit = 25;
        if(request()->has('limit')) {
            $input = (int)request('limit');
            if($input < 101 && $input > 9){
                $limit = $input;
            }
        }

        $data = $data->paginate($limit);
        $transformer = "\\App\\Transformers\\{$transformer}";

        return response()->json(
            collect($data)
                ->merge([
                    'data' => fractal()->collection($data)
                        ->parseIncludes($includes)
                        ->transformWith(new $transformer())
                ])
        );
    }
}
