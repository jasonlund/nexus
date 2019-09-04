<?php

namespace App\Observers;

use App\Models\ChannelCategory;

class ChannelCategoryObserver
{
    /**
     * Redorder all ChannelCategories when one is deleted to fill gaps.
     *
     * @return  void
     */
    public function deleted()
    {
        ChannelCategory::setNewOrder(ChannelCategory::ordered()->pluck('id')->toArray());
    }
}
