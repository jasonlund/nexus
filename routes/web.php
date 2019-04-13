<?php
Route::get('/test', function() {
    $service = new App\Services\ChannelsService();
    $user = App\Models\User::first();
    $channel = App\Models\Channel::first();
    auth()->login($user);

    dd($service->viewed($channel));
});
