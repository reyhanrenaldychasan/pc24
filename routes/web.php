<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/storage/image/{compressed}/{filename?}', function($filename) {

    // optionally do something with $filename here

    return response( file_get_contents(asset('img/placeholder.jpg')) )
        ->header('Content-Type','image/png');

});

Route::middleware(['auth'])->group(function () {
    Route::prefix('news')->group(function () {
        Route::get('/', 'App\Http\Controllers\NewsController@index')->name('news.index');
        Route::get('/search/{keyword}', 'App\Http\Controllers\NewsController@search')->name('news.index.search');
        Route::get('/create', 'App\Http\Controllers\NewsController@create')->name('news.create');
        Route::post('/store', 'App\Http\Controllers\NewsController@store')->name('news.store');
        Route::get('/{news}/edit', 'App\Http\Controllers\NewsController@edit')->name('news.edit');
        Route::post('/{news}/update', 'App\Http\Controllers\NewsController@update')->name('news.update');
        Route::get('/{news}/delete', 'App\Http\Controllers\NewsController@destroy')->name('news.delete');
    });

    Route::prefix('tags')->group(function () {
        Route::get('/', 'App\Http\Controllers\TagController@index')->name('tags.index');
        Route::get('/create', 'App\Http\Controllers\TagController@create')->name('tags.create');
        Route::post('/store', 'App\Http\Controllers\TagController@store')->name('tags.store');
        Route::get('/{tags}/edit', 'App\Http\Controllers\TagController@edit')->name('tags.edit');
        Route::post('/{tags}/update', 'App\Http\Controllers\TagController@update')->name('tags.update');
        Route::get('/{tags}/delete', 'App\Http\Controllers\TagController@destroy')->name('tags.delete');
    });
    
});
