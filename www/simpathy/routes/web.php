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

Auth::routes();

Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/home', 'HomeController@index');

Route::any('/jobs/list', 'HomeController@jobsData')->name('jobs-list');

Route::any('/jobs/{job}/log', 'HomeController@jobLog')->name('job-log');

Route::group(['prefix'    => 'simulation', 'middleware' => ['auth', 'role:user|administrator'],
              'namespace' => 'Simulation'], function () {
    Route::group(['prefix' => 'submit', 'middleware' => ['permission:create-job']], function () {
        Route::get('simple', 'SubmitController@submitSimple')->name('submit-simple');
        Route::get('enriched', 'SubmitController@submitEnriched')->name('submit-enriched');
    });
});
