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

Route::get('/home', 'HomeController@index')->name('user-home');

Route::any('/jobs/list', 'HomeController@jobsData')->name('jobs-list');

Route::any('/jobs/{job}/view', 'HomeController@viewJob')->middleware(['permission:read-job'])->name('job-view');

Route::any('/jobs/{job}/delete', 'HomeController@deleteJob')->middleware(['permission:delete-job'])->name('job-delete');

Route::any('/jobs/{job}/log', 'HomeController@jobLog')->middleware(['permission:read-job'])->name('job-log');

Route::group(['prefix'    => 'simulation', 'middleware' => ['auth', 'role:user|administrator'],
              'namespace' => 'Simulation'], function () {
    Route::group(['prefix' => 'submit', 'middleware' => ['permission:create-job']], function () {
        Route::get('simple', 'SubmitController@submitSimple')->name('submit-simple');
        Route::post('simple', 'SubmitController@doSubmitSimple')->name('do-submit-simple');
        Route::get('enriched', 'SubmitController@submitEnriched')->name('submit-enriched');
        Route::post('enriched', 'SubmitController@doSubmitEnriched')->name('do-submit-enriched');
        Route::match(['get', 'post'], 'list/nodes', 'SubmitController@listNodes')->name('list-nodes');
    });
    Route::get('{job}/view', 'SimulationController@viewSimulation')->middleware(['permission:read-job'])
         ->name('view-simulation-job');
    Route::any('{job}/view/pathways/list', 'SimulationController@simulationPathwayListData')
         ->middleware(['permission:read-job'])->name('view-pathway-list-data');
    Route::get('{job}/view/pathway/{pid}/view', 'SimulationController@viewPathway')
         ->middleware(['permission:read-job'])->name('view-pathway-results');
});
