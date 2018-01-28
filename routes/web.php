<?php

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
    return redirect('/home');
});

//table1
Route::get('/getDataTable1', 'Admin\IndexController@getDataTable1');
Route::get('/getDataTable2', 'Admin\IndexController@getDataTable2');
Route::get('/getDataTable3', 'Admin\IndexController@getDataTable3');
Route::get('/getDataTable4', 'Admin\IndexController@getDataTable4');


//api
Route::group(['prefix' => 'api'], function () {
    //table1
    Route::get('/uploadNumber', 'ApiController@uploadNumber');
    Route::get('/deleteNumber', 'ApiController@deleteNumber');

    //table2
    Route::get('/updateDeviceData', 'ApiController@updateDeviceData');
    Route::get('/getDeviceData', 'ApiController@getDeviceData');

    //table3
    Route::get('/addNumberTable3', 'ApiController@addNumberTable3');


    //table4
    Route::get('/addNumberTable4', 'ApiController@addNumberTable4');
    Route::get('/deleteNumberTable4', 'ApiController@deleteNumberTable4');
    Route::get('/updateNumberTable4', 'ApiController@updateNumberTable4');
    Route::get('/getNumberTable4', 'ApiController@getNumberTable4');


    //table5
    Route::get('/getNumberTable5', 'ApiController@getNumberTable5');




    //auto
    Route::get('/autoRunTable3', 'ApiController@autoRunTable3');

    Route::get('/autoTest', 'ApiController@autoTest');

    Route::get('/getMyUrl', 'ApiController@getMyUrl');
});












