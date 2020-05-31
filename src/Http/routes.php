<?php


Route::group(['middleware' => ['web']], function () {
    Route::prefix('cielo/standard')->group(function () {

        Route::get('/redirect', 'Lucena\Cielo\Http\Controllers\StandardController@redirect')->name('cielo.standard.redirect');

        Route::get('/success', 'Lucena\Cielo\Http\Controllers\StandardController@success')->name('cielo.standard.success');

        Route::get('/cancel', 'Lucena\Cielo\Http\Controllers\StandardController@cancel')->name('cielo.standard.cancel');
    });
});

Route::prefix('cielo')->group(function () {
    Route::post('/notification','Lucena\Cielo\Http\Controllers\StandardController@notification')->name('cielo.standard.notification');
});