<?php

// globally used app path, for all includes and requires
$path = realpath(dirname('__FILE__'));
define('APP_PATH', $path . '/');
define('APP_NAME', basename($path));

session_name(APP_NAME);
session_start();

require_once(APP_PATH . 'inc/init.php');
require_once(APP_PATH . 'inc/app.class.php');
log&&msg(__METHOD__, 'start up App');
$app = new App();
$app->dispatcher->respond();

// clean the cache folder (happens once a day)
$app->files->cleanup_cache();
log&&msg(__METHOD__, 'shutting App down');
?>