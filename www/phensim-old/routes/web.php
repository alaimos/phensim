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

Route::get(
    '/',
    static function () {
        return view('welcome');
    }
);

Auth::routes();

Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/home', 'HomeController@index')->name('user-home');

Route::get(
    '/references',
    static function () {
        return view('references');
    }
);

Route::get(
    '/help',
    static function () {
        return view('help');
    }
);

Route::get(
    '/contacts',
    static function () {
        return view('contacts');
    }
);

Route::any('/jobs/list', 'HomeController@jobsData')->name('jobs-list');

Route::any('/jobs/{job}/view', 'HomeController@viewJob')->middleware(['permission:read-job'])->name('job-view');

Route::any('/jobs/{job}/delete', 'HomeController@deleteJob')->middleware(['permission:delete-job'])->name('job-delete');

Route::any('/jobs/{job}/log', 'HomeController@jobLog')->middleware(['permission:read-job'])->name('job-log');

Route::group(
    [
        'prefix'     => 'simulation',
        'middleware' => ['auth', 'role:user|administrator'],
        'namespace'  => 'Simulation',
    ],
    static function () {
        Route::group(
            ['prefix' => 'submit', 'middleware' => ['permission:create-job']],
            static function () {
                Route::get('simple', 'SubmitController@submitSimple')->name('submit-simple');
                Route::post('simple', 'SubmitController@doSubmitSimple')->name('do-submit-simple');
                Route::get('enriched', 'SubmitController@submitEnriched')->name('submit-enriched');
                Route::post('enriched', 'SubmitController@doSubmitEnriched')->name('do-submit-enriched');
                Route::match(['get', 'post'], 'list/nodes', 'SubmitController@listNodes')->name('list-nodes');
            }
        );
        Route::get('{job}/view', 'SimulationController@viewSimulation')->middleware(['permission:read-job'])
             ->name('view-simulation-job');
        Route::get('{job}/download', 'SimulationController@downloadData')->middleware(['permission:read-job'])
             ->name('download-simulation-data');
        Route::get('{job}/download/pathway', 'SimulationController@downloadPathwayData')->middleware(['permission:read-job'])
             ->name('download-simulation-pathway-data');
        Route::get('{job}/download/nodes', 'SimulationController@downloadNodesData')->middleware(['permission:read-job'])
             ->name('download-simulation-nodes-data');
        Route::any('{job}/view/pathways/list', 'SimulationController@pathwaysListData')
             ->middleware(['permission:read-job'])->name('view-pathway-list-data');
        Route::get('{job}/view/pathway/{pid}/view', 'SimulationController@viewPathway')
             ->middleware(['permission:read-job'])->name('view-pathway-results');
        Route::any('{job}/view/pathway/{pid}/data', 'SimulationController@pathwayViewListData')
             ->middleware(['permission:read-job'])->name('sim-nodes-list-data');
    }
);

Route::get('/home/api', 'Api\ApiController@help')->middleware(
    [
        'auth',
        'role:user|administrator',
        'permission:use-api',
    ]
)->name('api-index');