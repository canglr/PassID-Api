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


Route::get('/surum', 'SurumController@surum');

Route::get('/mailto/{id}', 'SurumController@mail');

Route::get('/kullanicilar/kontrol', 'KullanicilarController@kontrol');

Route::get('/kullanicilar/dogrulama', 'KullanicilarController@dogrulama');

Route::get('/kullanicilar/oturum', 'KullanicilarController@oturum');

Route::get('/kullanicilar/oturumukapat', 'KullanicilarController@cihazlardancikisyap');

Route::post('/kullanicilar/cihaz', 'KullanicilarController@cihaz');

Route::post('/kullanicilar/giris', 'KullanicilarController@giris');

Route::post('/kullanicilar/geribildirim', 'KullanicilarController@geribildirim');

Route::get('/kullanicilar/googlekontrol', 'KullanicilarController@GoogleKontrol');



