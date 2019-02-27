<?php
Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);

Route::get('/profile', ['as' => 'self.show', 'uses' => 'UsersController@showSelf']);
Route::patch('/profile', ['as' => 'self.update', 'uses' => 'UsersController@updateSelf']);
Route::delete('/profile', ['as' => 'self.destroy', 'uses' => 'UsersController@destroySelf']);

Route::get('/users', ['as' => 'users.index', 'uses' => 'UsersController@index']);
Route::get('/users/{user}', ['as' => 'users.show', 'uses' => 'UsersController@show']);
Route::delete('/users/{user}', ['as' => 'users.destroy', 'uses' => 'UsersController@destroy']);
Route::patch('/users/{user}', ['as' => 'users.update', 'uses' => 'UsersController@update']);
Route::patch('/users/{user}/ban', ['as' => 'users.ban', 'uses' => 'UsersController@ban']);
Route::patch('/users/{user}/unban', ['as' => 'users.unban', 'uses' => 'UsersController@unban']);

Route::put('/register', ['as' => 'auth.register', 'uses' => 'Auth\RegisterController@register']);
Route::post('/login', ['as' => 'auth.login', 'uses' => 'Auth\LoginController@login']);
Route::post('/logout', ['as' => 'auth.logout', 'uses' => 'Auth\LoginController@logout']);

Route::get('/password/reset', ['as' => 'password.request', 'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm']);
Route::get('/password/reset/{token}', ['as' => 'password.reset',
    'uses' => 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm']);
Route::post('/password/email', ['as' => 'password.email', 'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
Route::post('/password/reset', ['as' => 'password.update', 'uses' => 'Auth\ResetPasswordController@reset']);

Route::get('/channels', ['as' => 'channels.index', 'uses' => 'ChannelsController@index']);
Route::get('/channels/{channel}', ['as' => 'channels.show', 'uses' => 'ChannelsController@show']);
Route::put('/channels', ['as' => 'channels.store', 'uses' => 'ChannelsController@store']);
Route::patch('/channels/{channel}', ['as' => 'channels.update', 'uses' => 'ChannelsController@update']);
Route::delete('/channels/{channel}', ['as' => 'channels.destroy', 'uses' => 'ChannelsController@destroy']);

Route::put('/channels/{channel}', ['as' => 'threads.store', 'uses' => 'ThreadsController@store']);
Route::get('/channels/{channel}/{thread}', ['as' => 'threads.show', 'uses' => 'ThreadsController@show']);
Route::patch('/channels/{channel}/{thread}', ['as' => 'threads.update', 'uses' => 'ThreadsController@update']);
Route::delete('/channels/{channel}/{thread}', ['as' => 'threads.destroy', 'uses' => 'ThreadsController@destroy']);

Route::put('/channels/{channel}/{thread}/replies', ['as' => 'replies.store', 'uses' => 'RepliesController@store']);
Route::patch('/channels/{channel}/{thread}/replies/{reply}', ['as' => 'replies.update', 'uses' => 'RepliesController@update']);
Route::delete('/channels/{channel}/{thread}/replies/{reply}', ['as' => 'replies.destroy', 'uses' => 'RepliesController@destroy']);

//Auth::routes();
