<?php

use App\Http\Controllers\Api\SimulationController;
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

Route::group(
    [
        'prefix'     => 'v1',
        'middleware' => 'auth:sanctum',
        'as'         => 'api.',
    ],
    static function () {
        Route::get('ping', fn(Request $request) => $request->user());
        Route::get('simulations/{simulation}/download/{type}', [SimulationController::class, 'download'])->name('simulations.download');
        Route::get('simulations/{simulation}/submit', [SimulationController::class, 'submit'])->name('simulations.submit');
        Route::apiResource('simulations', SimulationController::class)->except('update');
    }
);
