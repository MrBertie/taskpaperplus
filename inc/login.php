<?php
global $app;

/**
 * We only get here if user is not logged in
 * $app->user contains instance of user login class
 */

$req = $_POST;

if (isset($req['username'])) $username = $req['username'];
if (isset($req['password'])) $password = $req['password'];

if ($app->user->login($username, $password)) {
    
    // start app
    $app->dispatcher->respond();
    
} else {
    
    log&&msg('login failed');
    
    // new user
    
}
?>
