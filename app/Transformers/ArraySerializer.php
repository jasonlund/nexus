<?php

namespace App\Transformers;

use League\Fractal\Serializer\ArraySerializer as Base;

class ArraySerializer extends Base
{
    /**
     * Serialize into one array.
     *
     * @param string $resourceKey
     * @param array $data
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey) {
            return [$resourceKey => $data];
        }

        return $data;
    }

    /**
     * Serialize into one array.
     *
     * @param string $resourceKey
     * @param array $data
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        if ($resourceKey) {
            return [$resourceKey => $data];
        }
        return $data;
    }

    public function null()
    {
        return null;
    }
}
