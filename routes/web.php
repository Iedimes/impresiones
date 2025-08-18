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
    return view('auth.login');
});

Auth::routes([
    'reset' => false,
    'verify' => false,
    'register' => false,
]);

// Públicas
Route::get('/verificacion/{id}/', 'HomeController@verificacion');

// Protegidas
Route::middleware('auth')->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');

    Route::resource('photos', 'PhotoController');
    Route::get('pre/{cedula}', 'PhotoController@index');

    Route::post('/filtros', 'HomeController@index');
    Route::get('/filtros', 'HomeController@index');

    Route::get('generate/{id}/{idtipo}', 'FileController@imprimir');
    Route::get('previa/{id}/', 'HomeController@previaimpresion');
    Route::get('generatemasivo/', 'FonavisController@generateMasivo');
});
