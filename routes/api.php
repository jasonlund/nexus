<?php

Route::group(['middleware' => ['guest']], function() {
    Route::post('auth/login', ['as' => 'auth.login', 'uses' => 'Auth\TokenController@login']);

    Route::post('auth/register', ['as' => 'auth.register', 'uses' => 'Auth\RegisterController@register']);

    Route::post('password/email', ['as' => 'password.email', 'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
    Route::post('password/reset', ['as' => 'password.reset', 'uses' => 'Auth\ResetPasswordController@reset']);
});

Route::group(['middleware' => ['auth']], function() {
    Route::get('auth/refresh', ['as' => 'auth.refresh', 'uses' => 'Auth\TokenController@refresh']);
    Route::post('auth/logout', ['as' => 'auth.logout', 'uses' => 'Auth\TokenController@logout']);
});

Route::group(['middleware' => ['auth', 'refresh']], function() {
    Route::get('profile', ['as' => 'self.show', 'uses' => 'SelfController@show']);
    Route::patch('profile', ['as' => 'self.update', 'uses' => 'SelfController@update']);
    Route::delete('profile', ['as' => 'self.destroy', 'uses' => 'SelfController@destroy']);
    Route::post('profile/avatar', ['as' => 'self.avatar', 'uses' => 'SelfController@avatar']);

    Route::put('channels', ['as' => 'channels.store', 'uses' => 'ChannelsController@store']);
    Route::post('channels/reorder', ['as' => 'channels.reorder', 'uses' => 'ChannelsController@reorder']);
    Route::patch('channels/{channel}', ['as' => 'channels.update', 'uses' => 'ChannelsController@update']);
    Route::get('channels/{channel}/read', ['as' => 'channels.read', 'uses' => 'ChannelsController@markRead']);
    Route::delete('channels/{channel}', ['as' => 'channels.destroy', 'uses' => 'ChannelsController@destroy']);

    Route::put('channels/{channel}', ['as' => 'threads.store', 'uses' => 'ThreadsController@store']);
    Route::patch('channels/{channel}/threads/{thread}', ['as' => 'threads.update', 'uses' => 'ThreadsController@update']);
    Route::delete('channels/{channel}/threads/{thread}', ['as' => 'threads.destroy', 'uses' => 'ThreadsController@destroy']);

    Route::put('channels/{channel}/threads/{thread}/replies', ['as' => 'replies.store', 'uses' => 'RepliesController@store']);
    Route::patch('channels/{channel}/threads/{thread}/replies/{reply}', ['as' => 'replies.update', 'uses' => 'RepliesController@update']);
    Route::delete('channels/{channel}/threads/{thread}/replies/{reply}', ['as' => 'replies.destroy', 'uses' => 'RepliesController@destroy']);

    Route::get('users', ['as' => 'users.index', 'uses' => 'UsersController@index']);
    Route::get('users/{user}', ['as' => 'users.show', 'uses' => 'UsersController@show']);
    Route::delete('users/{user}', ['as' => 'users.destroy', 'uses' => 'UsersController@destroy']);
    Route::patch('users/{user}', ['as' => 'users.update', 'uses' => 'UsersController@update']);
    Route::post('users/{user}/avatar', ['as' => 'users.avatar', 'uses' => 'UsersController@avatar']);
    Route::patch('users/{user}/ban', ['as' => 'users.ban', 'uses' => 'UsersController@ban']);
    Route::patch('users/{user}/unban', ['as' => 'users.unban', 'uses' => 'UsersController@unban']);
});

Route::get('channels', ['as' => 'channels.index', 'uses' => 'ChannelsController@index']);
Route::get('channels/{channel}', ['as' => 'channels.show', 'uses' => 'ChannelsController@show']);

Route::get('channels/{channel}/threads', ['as' => 'threads.index', 'uses' => 'ThreadsController@index']);
Route::get('channels/{channel}/threads/{thread}', ['as' => 'threads.show', 'uses' => 'ThreadsController@show']);

Route::get('channels/{channel}/threads/{thread}/replies', ['as' => 'replies.index', 'uses' => 'RepliesController@index']);
