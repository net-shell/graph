<?php

$app->get('/', function () use ($app) {
    return view('home');
});

$app->get('translate', 'TranslateController@getIndex');