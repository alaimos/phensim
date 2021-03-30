<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PathwayController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\UserController;
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

$proxy_url = config('phensim.proxy_url');
$proxy_schema = config('phensim.proxy_scheme');

if (!empty($proxy_url)) {
    URL::forceRootUrl($proxy_url);
}

if (!empty($proxy_schema)) {
    URL::forceScheme($proxy_schema);
}

Route::view('/', 'welcome');

Auth::routes();

Route::group(
    ['middleware' => 'auth'],
    static function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::view('/profile', 'profile.edit')->name('profile.edit');
        Route::view('/simulations/create/simple', 'simulations.create.simple')->name('simulations.create.simple');
        Route::view('/simulations/create/advanced', 'simulations.create.advanced')->name('simulations.create.advanced');
        Route::get('/simulations/{simulation}/download/input', [SimulationController::class, 'downloadInput'])->name(
            'simulations.download.input'
        );
        Route::get('/simulations/{simulation}/download/output', [SimulationController::class, 'downloadOutput'])->name(
            'simulations.download.output'
        );
        Route::get('/simulations/{simulation}/download/pathway', [SimulationController::class, 'downloadPathway'])->name(
            'simulations.download.pathway'
        );
        Route::get('/simulations/{simulation}/download/node', [SimulationController::class, 'downloadNode'])->name(
            'simulations.download.node'
        );
        Route::get('/simulations/{simulation}/download/sbml', [SimulationController::class, 'downloadSbml'])->name(
            'simulations.download.sbml'
        );
        Route::get('/simulations/{simulation}/download/sif', [SimulationController::class, 'downloadSif'])->name(
            'simulations.download.sif'
        );
        Route::get('/simulations/{simulation}', [SimulationController::class, 'show'])->name('simulations.show');
        Route::get('/simulations/{simulation}/pathways/{pathway}', [PathwayController::class, 'show'])->name(
            'simulations.pathways.show'
        );
        Route::view('/simulations', 'simulations.index')->name('simulations.index');
        Route::view('/docs', 'docs.index')->name('docs.index');
        Route::view('/docs/api', 'docs.api')->name('docs.api');
        Route::view('/references', 'pages.references')->name('pages.references');
        Route::view('/contacts', 'pages.contacts')->name('pages.contacts');

        Route::group(
            ['middleware' => 'is.admin'],
            static function () {
                Route::view('users', 'users.index')->name('users.index');
                Route::view('messages', 'messages.index')->name('messages.index');
            }
        );
    }
);

