<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/line/webhook/message', 'App\Http\Controllers\LineWebhookController@message')->name('line.webhook.message');

Route::get('/test/google', 'App\Http\Controllers\LineWebhookController@google_checker')->name('test.google_checker');

Route::get('/Bank', 'App\Http\Controllers\Api\BankController@index')->name('bank');
Route::get('/Bank/create', 'App\Http\Controllers\Api\BankController@create')->name('bank');
Route::get('/Bank/list', 'App\Http\Controllers\Api\BankController@list')->name('bank');
Route::get('/Bank/check', 'App\Http\Controllers\Api\BankController@check')->name('bank');
Route::get('/Bank/month', 'App\Http\Controllers\Api\BankController@month')->name('bank');
Route::get('/Bank/virtualList', 'App\Http\Controllers\Api\BankController@virtualList')->name('bank');
Route::get('/Bank/makeVirtual', 'App\Http\Controllers\Api\BankController@makeVirtual')->name('bank');
Route::get('/BankCheck/check', 'App\Http\Controllers\Api\BankCheckController@check')->name('check');



