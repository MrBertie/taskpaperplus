<?php
namespace tpp;


const DEBUG_ON = './conf/_DEBUG_ON_';

if (file_exists(DEBUG_ON)) {
    define('DEBUG_MODE', true);
} else {
    define('DEBUG_MODE', false); 
}


if (DEBUG_MODE) {
    
    // PHP errors
    define('SHOW_ERRORS', true);
    // [PHP_ERROR] Error pretty printer for debugging (to webpage)
    define('PHP_ERROR', false);
    // Show debug messages: de&&bug(...)
    define('de', true);
    // Show performance|sequence logs:   log&&msg(...)
    define('log', false);
    
} else {
    
    // PRODUCTION / RELEASE MODE
    define('SHOW_ERRORS', false);
    define('PHP_ERROR', false);
    define('de', false);
    define('log', false);
    
}


if (SHOW_ERRORS) {
    // View all error and notices
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

if (PHP_ERROR) {
    require_once(APP_PATH . 'other/php_error.php');
    $opt = array(
        'catch_class_not_found' => false,
    );
    \php_error\reportErrors($opt);
}

// Main logging and debug printer (to file)
if (de || log) {
    require_once(APP_PATH . 'inc/logger.php');
}

// *******************************

function toggle_debug_mode() {
    if (file_exists(DEBUG_ON)) {
        unlink(DEBUG_ON);
    } else {
        $f = fopen(DEBUG_ON, 'c');
        fclose($f);
    }
}
?>
