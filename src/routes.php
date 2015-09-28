<?php

Route::group(['prefix' => 'userbehavior'], function () {
    Route::get('/all', function () {
        return json_encode(HazeDevelopment\UserBehavior::all());
    });

    Route::get('/valid', function () {
        return json_encode(HazeDevelopment\UserBehavior::getValidRoute());
    });

    Route::get('/last', function () {
        return json_encode(HazeDevelopment\UserBehavior::getLastBehavior());
    });

    Route::get('/last/url', function () {
        return json_encode(HazeDevelopment\UserBehavior::getLastUrl());
    });
    Route::get('/untracked', function () {
        return json_encode(HazeDevelopment\UserBehavior::getUntracked());
    });
});
