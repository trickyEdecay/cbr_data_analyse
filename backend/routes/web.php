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

$app->get('/', ["as" => "home",function () use ($app) {
    return $app->version();
}]);

$app->get('foo',[ "middleware" => "old", function () use ($app) {
    return "hey2";
}]);

$app->get('/questionsPageFill','Questions@getQuestionsPageFill');
$app->get('/playersPageFill','Players@getPlayersPageFill');
$app->get('/playerPageFill/{id}','Players@getPlayerPageFill');