<?php

Route::group(array('prefix' => 'userbehavior'), function()
{
    Route::get('/all', function(){
        return json_encode(HazeDevelopment\UserBehavior::all());
    });

    Route::get('/valid', function(){
        return json_encode(HazeDevelopment\UserBehavior::getValidRoute());
    });

    Route::get('/last', function(){
        return json_encode(HazeDevelopment\UserBehavior::getLastBehavior());
    });

    Route::get('/last/url', function(){
        return json_encode(HazeDevelopment\UserBehavior::getLastUrl());
    });
<<<<<<< HEAD

    Route::get('/untracked', function(){
        return json_encode(HazeDevelopment\UserBehavior::getUntracked());
    });
=======
>>>>>>> ce406138331b6af21ca5433c28436181bc78ec48
});