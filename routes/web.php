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
    return view('welcome');
    // $data = [
    //     'amount' => 345.80,
    //     'receipt' => 'MFH34hGT5',
    // ];
    // return view('emails.temp', ['data'=>$data]);
});
