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
    $router->post('/user/forgotPassword', ['uses' => 'AuthController@forgotPassword']);
    $router->post('/user/resetPassword', ['uses' => 'AuthController@resetPassword']);
    $router->post('/resendCode', ['uses' => 'AuthController@resendCode']);
    $router->post('/dev/save/challenge', ['uses' => 'DevController@saveChallenge']);
});

$router->group(["prefix" => "api", 'middleware' => 'jwt.auth'], function() use($router) {
    $router->get('/auth/me', ['uses' => 'AuthController@authMe']);
    $router->get('/user/settings', ['uses' => 'UserSettingsController@getSettings']);
    $router->get('/profile', ['uses' => 'ProfileController@getProfile']);
    $router->post('/auth/logout', ['uses' => 'AuthController@logout']);
    $router->post('/save/challenge', ['uses' => 'ChallengeController@saveChallenge']);
    $router->post('/user/updateSettings', ['uses' => 'UserSettingsController@updateSettings']);
    $router->post('/updateUser', ['uses' => 'AuthController@updateUser']);
    $router->post('/add/clap', ['uses' => 'ClapsController@addClap']);
    $router->post('/add/comment', ['uses' => 'CommentsController@addComment']);
    $router->get('/comments', ['uses' => 'CommentsController@getComments']);
    $router->get('/searchUsers', ['uses' => 'DiscoverController@searchUser']);
    $router->get('/searchResults', ['uses' => 'DiscoverController@searchResults']);
    $router->get('/flatUserResults', ['uses' => 'DiscoverController@flatUserResults']);
    $router->get('/follow', ['uses' => 'FriendController@makeFriends']);
    $router->get('/getHome', ['uses' => 'HomeController@getHome']);
    $router->post('/acceptRequest', ['uses' => 'FriendController@acceptRequest']);
    $router->post('/cancelRequest', ['uses' => 'FriendController@cancelRequest']);
    $router->get('/discoverData', ['uses' => 'DiscoverController@discoverData']);
    $router->get('/categoryData', ['uses' => 'DiscoverController@categoryData']);
    $router->get('/season1Data', ['uses' => 'Season1Controller@season1Data']);
    $router->get('/savedChallenges', ['uses' => 'ChallengeController@getSavedChallenges']);
    $router->post('/deleteDraft', ['uses' => 'ChallengeController@deleteDraft']);
    $router->post('/uploadS3', ['uses' => 'ChallengeController@uploadS3Api']);
    $router->get('/sponsor/challenge', ['uses' => 'SponsorController@getSponsorChallenge']);
    $router->get('/sponsor/challenge/posts', ['uses' => 'SponsorController@getSponsorChallengePosts']);
    $router->post('/getFriends', ['uses' => 'FriendController@getFreinds']);
    $router->post('/saveHunt', ['uses' => 'HuntController@saveHunt']);
    $router->get('/getHunts', ['uses' => 'HuntController@getHunts']);
    $router->get('/huntDetail', ['uses' => 'HuntController@huntDetail']);
    $router->post('/joinHunt', ['uses' => 'HuntController@joinHunt']);
    $router->get('/huntSnapOffs', ['uses' => 'HuntController@getHuntChallengePosts']);
    $router->post('/exponent/devices/subscribe', ['uses' => 'ExpoController@saveExpoToken']);
    $router->post('/exponent/devices/unsubscribe', ['uses' => 'ExpoController@removeExpoToken']);

    $router->get('/followers', ['uses' => 'FriendController@getFollowers']);
    $router->get('/followings', ['uses' => 'FriendController@getFollowings']);

    $router->get('/activities', ['uses' => 'ActivityController@getActivities']);
    $router->get('/only/activities', ['uses' => 'ActivityController@onlyActivities']);


});
