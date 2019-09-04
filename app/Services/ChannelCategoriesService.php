<?php

namespace App\Services;

use App\Models\ChannelCategory;

class ChannelCategoriesService
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
            'name' => ['bail', 'required', 'string', 'max:100'],
            'order' => ['bail', 'required', 'array', 'exists:channel_categories,slug'],
        ]);

        switch ($action) {
            case "create":
                $rules = $rules->only(['name']);
                break;

            case "update":
                $rules = $rules->only(['name']);
                break;

            case "reorder":
                $rules = $rules->only(['order']);
                break;
        }

        return $rules->toArray();
    }

    /**
     * Create a new ChannelCategory
     *
     * @param   array  $data
     *
     * @return  ChannelCategory
     */
    public function create($data)
    {
        return ChannelCategory::create([
            'name' => $data['name']
        ]);
    }

    /**
     * Update an existing ChannelCategory
     *
     * @param   ChannelCategory  $category
     * @param   array            $data
     *
     * @return  ChannelCategory
     */
    public function update(ChannelCategory $category, $data)
    {
        $category->update([
            'name' => $data['name']
        ]);

        return $category;
    }

    /**
     * Reorder all ChannelCategories
     *
     * @param   array  $order
     *
     * @return  void
     */
    public function reorder($order)
    {
        $categories = ChannelCategory::whereIn('slug', $order)->get()
            ->sortBy(function ($item) use ($order) {
                return array_search($item->slug, $order);
            });

        ChannelCategory::setNewOrder($categories->pluck('id')->toArray());
    }
}
