<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('whatsapp-ping', 'WappinController@ping');
Route::get('whatsapp-get-external-ip', 'WappinController@getExternalIP');
Route::post('whatsapp-get-token', 'WappinController@getToken');
Route::post('whatsapp-send-notification', 'WappinController@sendNotification');
Route::post('whatsapp-send-notification-media', 'WappinController@sendNotificationMedia');
Route::post('whatsapp-send-message', 'WappinController@sendMessage');
Route::post('whatsapp-send-message-media', 'WappinController@sendMessageMedia');
Route::post('whatsapp-inquiry', 'WappinController@inquiry');
Route::get('whatsapp-callback', 'WappinController@callback');
Route::post('whatsapp-callback', 'WappinController@callback');
Route::get('whatsapp-webhook', 'WappinController@webhook');
Route::post('whatsapp-webhook', 'WappinController@webhook');
