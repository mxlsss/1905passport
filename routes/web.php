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
    return view('welcome');
});

Route::get('/pash','Github\GithubController@pash');
//登陆
Route::post('/login','Github\GithubController@login');
//注册
Route::post('/login/reg','Github\GithubController@reg');

//鉴权
Route::post('/auth','Github\GithubController@auth');

Route::post('/getuserinfo','Github\GithubController@getuserinfo');


Route::post('/login/reg','Github\GithubController@reg');
//查询
Route::get('/login/onelist','Github\GithubController@onelist');

//验签
Route::get('/yq','Github\GithubController@yq');
//验签
Route::post('/yq2','Github\GithubController@yq2');
//公钥私钥解密
Route::get('/jiemi','Github\GithubController@jiemi');
//对称解密
Route::get('/jiemi2','Github\GithubController@jiemi2');

