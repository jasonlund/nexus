<?php
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
