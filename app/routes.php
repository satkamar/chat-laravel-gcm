<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

Route::post('chat/user','HomeController@registerUser');
Route::get('chat/users','HomeController@getAllUser');
Route::post('chat/message','HomeController@sendNotification');
Route::post('chat/user/gcm/{phone}/{regId}','HomeController@updateRegId');
//Route::post('uploadImage','HomeController@uploadImage');
Route::post('uploadImage','HomeController@uploadImageForAndroid');
Route::post('chat/profile','HomeController@updateProfileImagePath');

Route::post('creategroup','GroupController@createGroup');
Route::post('addmembers','GroupController@addGroupMembersinExixtingGroup');
Route::get('getGroupInfo','GroupController@getGroupInfo');
Route::post('removemember','GroupController@removeMember'); 
Route::post('sendGroupMessage','GroupController@sendGroupNotification'); 
Route::get('getUserGroup','GroupController@getUserGroup');
Route::post('signIn','HomeController@signInUser');