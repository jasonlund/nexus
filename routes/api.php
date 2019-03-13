<?php

Route::group(['middleware' => ['guest']], function() {
    Route::post('auth/login', ['as' => 'auth.login', 'uses' => 'Auth\TokenController@login']);

    Route::post('auth/register', ['as' => 'auth.register', 'uses' => 'Auth\RegisterController@register']);

    Route::post('password/email', ['as' => 'password.email', 'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
    Route::post('password/reset', ['as' => 'password.update', 'uses' => 'Auth\ResetPasswordController@reset']);
});

Route::group(['middleware' => ['auth']], function() {
    Route::post('auth/refresh', ['as' => 'auth.refresh', 'uses' => 'Auth\TokenController@refresh']);
    Route::post('auth/logout', ['as' => 'auth.logout', 'uses' => 'Auth\TokenController@logout']);
});

Route::group(['middleware' => ['auth', 'refresh']], function() {
    Route::get('profile', ['as' => 'self.show', 'uses' => 'UsersController@showSelf']);
    Route::patch('profile', ['as' => 'self.update', 'uses' => 'UsersController@updateSelf']);
    Route::delete('profile', ['as' => 'self.destroy', 'uses' => 'UsersController@destroySelf']);

    Route::put('channels', ['as' => 'channels.store', 'uses' => 'ChannelsController@store']);
    Route::patch('channels/{channel}', ['as' => 'channels.update', 'uses' => 'ChannelsController@update']);
    Route::delete('channels/{channel}', ['as' => 'channels.destroy', 'uses' => 'ChannelsController@destroy']);

    Route::put('channels/{channel}', ['as' => 'threads.store', 'uses' => 'ThreadsController@store']);
    Route::patch('channels/{channel}/{thread}', ['as' => 'threads.update', 'uses' => 'ThreadsController@update']);
    Route::delete('channels/{channel}/{thread}', ['as' => 'threads.destroy', 'uses' => 'ThreadsController@destroy']);

    Route::put('channels/{channel}/{thread}/replies', ['as' => 'replies.store', 'uses' => 'RepliesController@store']);
    Route::patch('channels/{channel}/{thread}/replies/{reply}', ['as' => 'replies.update', 'uses' => 'RepliesController@update']);
    Route::delete('channels/{channel}/{thread}/replies/{reply}', ['as' => 'replies.destroy', 'uses' => 'RepliesController@destroy']);

    Route::get('users', ['as' => 'users.index', 'uses' => 'UsersController@index']);
    Route::get('users/{user}', ['as' => 'users.show', 'uses' => 'UsersController@show']);
    Route::delete('users/{user}', ['as' => 'users.destroy', 'uses' => 'UsersController@destroy']);
    Route::patch('users/{user}', ['as' => 'users.update', 'uses' => 'UsersController@update']);
    Route::patch('users/{user}/ban', ['as' => 'users.ban', 'uses' => 'UsersController@ban']);
    Route::patch('users/{user}/unban', ['as' => 'users.unban', 'uses' => 'UsersController@unban']);
});

Route::get('channels', ['as' => 'channels.index', 'uses' => 'ChannelsController@index']);
Route::get('channels/{channel}', ['as' => 'channels.show', 'uses' => 'ChannelsController@show']);

Route::get('channels/{channel}/threads', ['as' => 'threads.index', 'uses' => 'ThreadsController@index']);
Route::get('channels/{channel}/{thread}', ['as' => 'threads.show', 'uses' => 'ThreadsController@show']);

Route::get('channels/{channel}/{thread}/replies', ['as' => 'replies.index', 'uses' => 'RepliesController@index']);
