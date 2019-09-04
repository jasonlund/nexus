<?php

namespace App\Observers;

use App\Models\Emote;
use Illuminate\Support\Facades\Storage;
use Cache;

class EmoteObserver
{
    /**
     * Update cachce when a new emote is added.
     *
     * @param   Emote  $emote
     *
     * @return  void
     */
    public function created()
    {
        $this->cacheEmotes();
    }

    /**
     * Remove file before an emote is deleted.
     *
     * @param   Emote  $emote
     *
     * @return  void
     */
    public function deleting(Emote $emote)
    {
        Storage::disk('public')->delete($emote->path);
    }

    /**
     * Update cahce when an emote is deleted.
     *
     * @param   Emote  $emote
     *
     * @return  void
     */
    public function deleted(Emote $emote)
    {
        $this->cacheEmotes();
    }

    /**
     * Cache the JSON response for Emotes.
     *
     * @return  void
     */
    private function cacheEmotes()
    {
        Cache::forever('emotes', Emote::response());
    }
}
