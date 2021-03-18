<?php

use App\Http\Controllers\HomeController;
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

Route::view('/', 'welcome');

Auth::routes();

Route::group(
    ['middleware' => 'auth'],
    static function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::view('/profile', 'profile.edit')->name('profile.edit');
        Route::view('/simulations/create/simple', 'simulations.create.simple')->name('simulations.create.simple');
        Route::view('/simulations/create/advanced', 'simulations.create.advanced')->name('simulations.create.advanced');
        Route::get('/simulations/{simulation}', [SimulationController::class, 'show'])->name('simulations.show');
        Route::view('/simulations', 'simulations.index')->name('simulations.index');

        Route::group(
            ['middleware' => 'is.admin'],
            static function () {
                Route::resource('/user', UserController::class)->except(['show']);
            }
        );
    }
);

