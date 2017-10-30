<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

Route::group(['middleware' => 'auth'], function() {

    Route::group(['prefix' => 'buildings'], function() {
        Route::post('/', 'BuildingController@store');
        Route::get('/', 'BuildingController@index');
        Route::delete('{id}', 'BuildingController@delete');
        Route::put('{id}', 'BuildingController@update');
    });

    Route::group(['prefix' => 'buildingsDevelopers'], function() {
        Route::post('/', 'BuildingDeveloperController@store');
        Route::get('/', 'BuildingDeveloperController@index');
        Route::delete('{id}', 'BuildingDeveloperController@delete');
        Route::put('{id}', 'BuildingDeveloperController@update');
    });

    // Route::post('building/{id}/buildingsUnit', 'BuildingUnitController@store');
        Route::get('buildingsUnit', 'BuildingUnitController@index');
        // Route::group(['prefix' => 'buildingsUnit'], function() {
            
            // Route::delete('{id}', 'BuildingUnitController@delete');
            // Route::put('{id}', 'BuildingUnitController@update');
        // });

    Route::post('building/{id}/buildingMetadata', 'BuildingMetadataController@store');

    Route::group(['prefix' => 'buildingMetadata'], function() {
        Route::get('/', 'BuildingMetadataController@index');
        Route::delete('{id}', 'BuildingMetadataController@delete');
        Route::put('{id}', 'BuildingMetadataController@update');
    });
});
