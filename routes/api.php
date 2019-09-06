<?php

Route::group(['middleware' => ['guest']], function() {
    Route::post('auth/login',
        ['as' => 'auth.login', 'uses' => 'Auth\TokenController@login']);

    Route::post('auth/register',
        ['as' => 'auth.register', 'uses' => 'Auth\RegisterController@register']);

    Route::post('password/email',
        ['as' => 'password.email', 'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
    Route::post('password/reset/{token}',
        ['as' => 'password.reset', 'uses' => 'Auth\ResetPasswordController@reset']);
});

Route::get('auth/refresh',
    ['as' => 'auth.refresh', 'uses' => 'Auth\TokenController@refresh']);

Route::group(['middleware' => ['auth']], function() {
    Route::post('auth/logout',
        ['as' => 'auth.logout', 'uses' => 'Auth\TokenController@logout']);
});

Route::group(['middleware' => ['auth', 'refresh']], function() {
    Route::put('categories/store',
        ['as' => 'categories.store', 'uses' => 'ChannelCategoriesController@store']);
    Route::post('categories/reorder',
        ['as' => 'categories.reorder', 'uses' => 'ChannelCategoriesController@reorder']);
    Route::patch('categories/update/{category}',
        ['as' => 'categories.update', 'uses' => 'ChannelCategoriesController@update']);
    Route::delete('categories/destroy/{category}',
        ['as' => 'categories.destroy', 'uses' => 'ChannelCategoriesController@destroy']);

    Route::put('channels/store/{category}',
        ['as' => 'channels.store', 'uses' => 'ChannelsController@store']);
    Route::post('channels/reorder/{category}',
        ['as' => 'channels.reorder', 'uses' => 'ChannelsController@reorder']);
    Route::patch('channels/update/{category}/{channel}',
        ['as' => 'channels.update', 'uses' => 'ChannelsController@update']);
    Route::get('channels/read/{category}/{channel}',
        ['as' => 'channels.read', 'uses' => 'ChannelsController@markRead']);
    Route::delete('channels/destroy/{category}/{channel}',
        ['as' => 'channels.destroy', 'uses' => 'ChannelsController@destroy']);

    Route::post('emotes/store',
        ['as' => 'emotes.store', 'uses' => 'EmotesController@store']);
    Route::delete('emotes/destroy/{emote}',
        ['as' => 'emotes.destroy', 'uses' => 'EmotesController@destroy']);

    Route::post('images/store',
        ['as' => 'images.store', 'uses' => 'ImagesController@store']);

    Route::get('profile/show',
        ['as' => 'self.show', 'uses' => 'SelfController@show']);
    Route::patch('profile/update',
        ['as' => 'self.update', 'uses' => 'SelfController@update']);
    Route::delete('profile/destroy',
        ['as' => 'self.destroy', 'uses' => 'SelfController@destroy']);
    Route::post('profile/avatar',
        ['as' => 'self.avatar', 'uses' => 'SelfController@avatar']);

    Route::put('replies/store/{category}/{channel}/{thread}',
        ['as' => 'replies.store', 'uses' => 'RepliesController@store']);
    Route::patch('replies/update/{category}/{channel}/{thread}/{reply}',
        ['as' => 'replies.update', 'uses' => 'RepliesController@update']);
    Route::delete('replies/destroy/{category}/{channel}/{thread}/{reply}',
        ['as' => 'replies.destroy', 'uses' => 'RepliesController@destroy']);

    Route::put('threads/store/{category}/{channel}',
        ['as' => 'threads.store', 'uses' => 'ThreadsController@store']);
    Route::patch('threads/update/{category}/{channel}/{thread}',
        ['as' => 'threads.update', 'uses' => 'ThreadsController@update']);
    Route::delete('threads/destroy/{category}/{channel}/{thread}',
        ['as' => 'threads.destroy', 'uses' => 'ThreadsController@destroy']);
    Route::post('threads/lock/{category}/{channel}/{thread}',
        ['as' => 'threads.lock', 'uses' => 'ThreadsController@lock']);

    Route::delete('users/destroy/{user}',
        ['as' => 'users.destroy', 'uses' => 'UsersController@destroy']);
    Route::patch('users/update/{user}',
        ['as' => 'users.update', 'uses' => 'UsersController@update']);
    Route::post('users/avatar/{user}',
        ['as' => 'users.avatar', 'uses' => 'UsersController@avatar']);
    Route::patch('users/ban/{user}',
        ['as' => 'users.ban', 'uses' => 'UsersController@ban']);
    Route::patch('users/unban/{user}',
        ['as' => 'users.unban', 'uses' => 'UsersController@unban']);
});

Route::get('emotes/index',
    ['as' => 'emotes.index', 'uses' => 'EmotesController@index']);

Route::get('categories/index',
    ['as' => 'categories.index', 'uses' => 'ChannelCategoriesController@index']);

Route::get('channels/index/{category}',
    ['as' => 'channels.index', 'uses' => 'ChannelsController@index']);
Route::get('channels/show/{category}/{channel}',
    ['as' => 'channels.show', 'uses' => 'ChannelsController@show']);

Route::get('threads/index/{category}/{channel}',
    ['as' => 'threads.index', 'uses' => 'ThreadsController@index']);
Route::get('threads/show/{category}/{channel}/{thread}',
    ['as' => 'threads.show', 'uses' => 'ThreadsController@show']);

Route::get('replies/index/{category}/{channel}/{thread}',
    ['as' => 'replies.index', 'uses' => 'RepliesController@index']);

Route::get('users/index',
    ['as' => 'users.index', 'uses' => 'UsersController@index']);
Route::get('users/show/{user}',
    ['as' => 'users.show', 'uses' => 'UsersController@show']);
