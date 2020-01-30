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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(["prefix" => "api"], function() use($router) {
    $router->post('/auth/register', ['uses' => 'AuthController@register']);
    $router->post('/auth/verifyCode', ['uses' => 'AuthController@verifyCode']);
    $router->post('/auth/login', ['uses' => 'AuthController@login']);
    $router->post('/auth/social/facebook', ['uses' => 'AuthController@facebookLogin']);
});

$router->group(["prefix" => "api", 'middleware' => 'jwt.auth'], function() use($router) {
    $router->get('/auth/me', ['uses' => 'AuthController@authMe']);
    $router->get('/user/settings', ['uses' => 'UserSettingsController@getSettings']);
    $router->get('/profile', ['uses' => 'ProfileController@getProfile']);
    $router->post('/auth/logout', ['uses' => 'AuthController@logout']);
    $router->post('/save/challenge', ['uses' => 'ChallengeController@saveChallenge']);
    $router->post('/user/updateSettings', ['uses' => 'UserSettingsController@updateSettings']);
    $router->post('/add/clap', ['uses' => 'ClapsController@addClap']);
    $router->post('/add/comment', ['uses' => 'CommentsController@addComment']);
    $router->get('/comments', ['uses' => 'CommentsController@getComments']);
    $router->get('/searchUsers', ['uses' => 'DiscoverController@searchUser']);
    $router->get('/searchResults', ['uses' => 'DiscoverController@searchResults']);
    $router->get('/flatUserResults', ['uses' => 'DiscoverController@flatUserResults']);
    $router->get('/follow', ['uses' => 'FriendController@makeFriends']);
    $router->get('/getHome', ['uses' => 'HomeController@getHome']);
});
