<?php

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ .'/../vendor/google/apiclient/src');
require_once __DIR__.'/../vendor/autoload.php';
include __DIR__.'/../config/db.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

const CLIENT_ID = '[[TBA]]';
const CLIENT_SECRET = '[[TBA]]';
const APPLICATION_NAME = "[[TBA]]";

$client = new Google_Client();
$client->setApplicationName(APPLICATION_NAME);
$client->setClientId(CLIENT_ID);
$client->setClientSecret(CLIENT_SECRET);
$client->setRedirectUri('postmessage');
$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email'));

$plus = new Google_Service_Plus($client);

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__,
));
$app->register(new Silex\Provider\SessionServiceProvider());

// Initialize a session for the current user, and render main.html.
$app->get('/', function () use ($app) {
    $state = md5(rand());
    $app['session']->set('state', $state);
    return $app['twig']->render('main.html', array(
        'CLIENT_ID' => CLIENT_ID,
        'STATE' => $state,
        'APPLICATION_NAME' => APPLICATION_NAME
    ));
});

// Upgrade given auth code to token, and store it in the session.
// POST body of request should be the authorization code.
$app->post('/connect', function (Request $request) use ($app, $client, $plus) {
    $token = $app['session']->get('token');

    // Ensure that this is no request forgery going on, and that the user
    // sending us this connect request is the user that was supposed to.
    if ($request->get('state') != ($app['session']->get('state'))) {
        return new Response('Invalid state parameter', 401);
    }

    $app['session']->set('OPENID_AUTH', true);

    $code = $request->getContent();
    $gPlusId = $request->get['gplus_id'];
    // Exchange the OAuth 2.0 authorization code for user credentials.
    $client->authenticate($code);
    $token = json_decode($client->getAccessToken());
    
    // Verify the token
    $reqUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' .
          $token->access_token;
    $req = new Google_Http_Request($reqUrl);
    
    $tokenInfo = json_decode(
      $client->getAuth()->authenticatedRequest($req)->getResponseBody());
    
    // If there was an error in the token info, abort.
    if ($tokenInfo->error) {
    return new Response($tokenInfo->error, 500);
    }
    // Make sure the token we got is for the intended user.
    if ($tokenInfo->userid != $gPlusId) {
    return new Response(
        "Token's user ID doesn't match given user ID", 401);
    }
    // Make sure the token we got is for our app.
    if ($tokenInfo->audience != CLIENT_ID) {
    return new Response(
        "Token's client ID does not match app's.", 401);
    }

    // Store the token in the session for later use.
    $app['session']->set('token', json_encode($token));
    $id=4;
    // get email
    //echo $plus->people;
    $email = $plus->people->get("me");
    $email = $email['emails'];
    $email = $email[0];
    $email = $email['value'];
    
    //find id in database or insert and get id
    $db = connect();
    $query = "INSERT INTO users SET openid='$email' ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)";
    mysqli_query($db, $query);
    $id = mysqli_insert_id($db);
    disconnect($db);
    $app['session']->set('openid', $id);

    $response = "Successfully connected with token: " . print_r($token, true);

    return new Response($response, 200);
});

// Revoke current user's token and reset their session.

$app->post('/logout', function () use ($app) {
    try{
        // Remove the credentials from the user's session.
        $app['session']->set('token', '');
        $app['session']->set('OPENID_AUTH', false);
        $app['session']->set('openid', -1);
        return new Response('Successfully logged out', 200);
    }
    catch(Exception $e){
        return new Response('error '.$e, 200);
    }
});

$app->run();