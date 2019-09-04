<?php

if (!function_exists('item_response')) {
    /**
     * Transform a given item and return a JSON response.
     *
     * @param   mixed  $item
     * @param   mixed  $transformer
     * @param   array  $includes
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    function item_response($item, $transformer, $includes = [])
    {
        $transformer = "\\App\\Transformers\\{$transformer}";

        return response()->json(fractal()
            ->item($item)
            ->parseIncludes($includes)
            ->transformWith(new $transformer()));
    }
}

if (!function_exists('collection_response')) {
    /**
     * Transform a given collection and return a JSON response.
     *
     * @param   array  $data
     * @param   mixed  $transformer
     * @param   array  $includes
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    function collection_response($data, $transformer, $includes = [])
    {
        $data = $data->get();
        $transformer = "\\App\\Transformers\\{$transformer}";

        return response()->json(fractal()
            ->collection($data)
            ->parseIncludes($includes)
            ->transformWith(new $transformer()));
    }
}

if (!function_exists('paginated_response')) {
    /**
     * Transform and paginate a given collection and return a JSON response.
     *
     * @param   array  $data
     * @param   mixed  $transformer
     * @param   array  $includes
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    function paginated_response($data, $transformer, $includes = [])
    {
        $limit = 25;
        if (request()->has('limit')) {
            $input = (int) request('limit');
            if ($input < 101 && $input > 9) {
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

if (!function_exists('strip_html_whitespace')) {
    /**
     * Strip all HTML from the given string.
     *
     * @param   string  $string
     *
     * @return  string
     */
    function strip_html_whitespace($string)
    {
        $string = strip_tags($string, '<iframe><img>');
        $string = preg_replace('/\s/', '', $string);
        $string = preg_replace('~\x{00a0}~', '', $string);

        return $string;
    }
}
