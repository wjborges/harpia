<?php

Route::group(['prefix' => 'monitoramento', 'middleware' => ['auth']], function () {
    Route::get('/', '\Modulos\Monitoramento\Http\Controllers\IndexController@getIndex')->name('monitoramento.index.index');

    Route::group(['prefix' => 'tempoonline'], function () {
        Route::get('/', '\Modulos\Monitoramento\Http\Controllers\TempoOnlineController@getIndex')->name('monitoramento.tempoonline.index');
        Route::get('/monitorar/{id}', '\Modulos\Monitoramento\Http\Controllers\TempoOnlineController@getMonitorar')->name('monitoramento.tempoonline.monitorar');
    });

    Route::group(['prefix' => 'forumresponse'], function () {
        Route::get('/', '\Modulos\Monitoramento\Http\Controllers\ForumController@getIndex')->name('monitoramento.forumresponse.index');
        Route::get('/monitorar/{id}', '\Modulos\Monitoramento\Http\Controllers\ForumController@getMonitorar')->name('monitoramento.forumresponse.monitorar');
    });
});
