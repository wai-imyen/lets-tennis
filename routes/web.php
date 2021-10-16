<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineBotController;
use App\Http\Controllers\SportradarTennisController;

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


// Route::post('/callback', 'App\Http\Controllers\LineController@webhook');
Route::post('/callback', [LineBotController::class, 'webhook']);

Route::get('/updateCompetitor', [SportradarTennisController::class, 'updateCompetitor']);

Route::get('/test', [LineBotController::class, 'test']);