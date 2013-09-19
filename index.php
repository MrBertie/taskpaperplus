<?php
namespace tpp;

// basic app setup
require_once('inc/init.php');

log&&msg('starting up App');

// start the app (internal API) and the respond to user input
$app = new App();
$app->dispatcher->start();

// clean the cache folder (this happens once a day only)
$app->cache->cleanup();

log&&msg('shutting App down');