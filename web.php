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
Route::get('/', 'Home@index');

Route::get('/recommend', 'Recommend@index');
Route::post('/recommend/current','Recommend@getRecommend');
Route::post('/recommend/create', 'Recommend@create');
Route::put('/recommend/edit', 'Recommend@edit');
Route::delete('/recommend/delete/{id}', 'Recommend@delete');


Route::get('/event', 'Events@index');
Route::get('/event/all','Events@getNearlyEvent' );
Route::post('/event/current','Events@getRecommend');
Route::post('/event/create', 'Events@create');
Route::put('/event/edit', 'Events@edit');
Route::delete('/event/delete/{id}', 'Events@delete');


Route::get('/action', 'Action@index');
Route::post('/action/create', 'Action@create');
Route::post('/action/delete/{id}', 'Action@delete');
