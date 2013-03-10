<?php
namespace tpp;

// basic app setup
require_once('inc/init.php');

log&&msg(__METHOD__, 'starting up App');

// start the app (API) and the respond to user input
$app = new App();
$app->dispatcher->respond();

// clean the cache folder (this happens once a day only)
$app->cache->cleanup();

log&&msg(__METHOD__, 'shutting App down');
?>