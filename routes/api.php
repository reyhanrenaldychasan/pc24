<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;

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

Route::post('/login', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');

Route::get('/news/{news}', '\App\Http\Controllers\api\NewsController@show');
Route::get('/news/{order?}/{tag?}/{offset?}/{limit?}', '\App\Http\Controllers\api\NewsController@index');
Route::get('/news/sort-date/{order}/{offset?}/{limit?}', '\App\Http\Controllers\api\NewsController@sortByDate');
Route::get('/news/sort-date-with-tag/{order}/{tag}/{offset?}/{limit?}', '\App\Http\Controllers\api\NewsController@sortByDateWithTag');
Route::post('/news-content-image', '\App\Http\Controllers\api\NewsController@contentUploadImage');

Route::get('/tags', '\App\Http\Controllers\api\TagController@index');


Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/logout', function(Request $request) {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    });

    Route::prefix('news')->group(function () {
        Route::post('/{news}/update', '\App\Http\Controllers\api\NewsController@update');
    });

});