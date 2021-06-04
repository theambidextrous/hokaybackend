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

Route::get('/', function (Request $request) {
    return response(['status' => 499, 'message' => 'point of no return']);
});
Route::fallback(function () {
    return response(['status'=> 499, 'message' => 'oops! Congrats! you\'ve reached point of no return']);
});
/** users */
Route::prefix('/users')->group( function() {
    Route::post('/login', 'UserController@signin')->name('signin');
    Route::post('/add', 'UserController@signup')->name('signup');
    Route::post('/sub/alerts', 'UserController@u_alert')->name('u_alert');
    Route::middleware('auth:api')->group( function(){
        Route::post('/is/active', 'UserController@is_active')->name('is_active');
        Route::post('/info', 'UserController@info')->name('info');
    });
});

/** Jobs find --new */
Route::middleware('throttle:1000000,1')->group(function () {
    Route::prefix('/find/jobs')->group( function() {
        Route::get('/page/{page}/limit/{limit}', 'JobController@findInifinite')->name('findInifinite');
    });
});
/** Jobs search --new */
Route::middleware('throttle:1000000,1')->group(function () {
    Route::prefix('/search')->group( function() {
        Route::get('/jobs/{keyword}/loc/{location}/page/{page}/limit/{limit}', 'JobController@searchInifinite')->name('searchInifinite');
    });
});

/** jobs */
Route::middleware('throttle:1000000,1')->group(function () {
    Route::prefix('/jobs')->group( function() {
        Route::post('/add/mann', 'JobController@add_manual')->name('add_manual');
        Route::post('/edit/mann/{editlink}', 'JobController@edit_manual')->name('edit_manual');
        Route::post('/add', 'JobController@add')->name('j_add');
        Route::get('/find/{jobid}', 'JobController@findOne')->name('j_findone');
        Route::get('/find/for/edit/{editlink}', 'JobController@findby_editlink')->name('findby_editlink');
        Route::get('/find/all/list/{offset}', 'JobController@findAll')->name('j_findall');
        Route::get('/find/by/{tag}/list/{offset}', 'JobController@by_tag')->name('by_tag');
        Route::get('/find/co/{co}/list/{offset}', 'JobController@by_company')->name('by_company');
        Route::get('/search/all/list/{keyword}', 'JobController@searchAll')->name('j_searchall');
        /** bots - bsw */
        Route::get('/bsw/from', 'JobController@get_bsw_from');
        Route::post('/bsw/from', 'JobController@update_bsw_from');
        /** bots - cigna */
        Route::get('/cigna/from', 'JobController@get_cigna_from');
        Route::post('/cigna/from', 'JobController@update_cigna_from');
    
        Route::middleware('auth:api')->group( function(){
            // Route::post('/add', 'JobController@add')->name('j_add');
            Route::put('/update/{jobid}', 'JobController@update')->name('j_update');
            Route::delete('/delete/{jobid}', 'JobController@delete')->name('j_delete');
        });
    });
});