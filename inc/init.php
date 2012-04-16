<?php
/*
 * Basic initialisation, config
 */

define('de', true);            // if we want debug messages logged de&&bug(...)    (see logger.txt)
define('log', false);           // show performance|sequence logs   log&&msg(...)   (see logger.txt)
define('SHOW_ERRORS', false);   // to show all errors and notices (on page)

if (SHOW_ERRORS) {
    // View all error and notices
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

// default extension for all taskpaper files (txt is probably best)
define('EXT', ".txt");

// item types within a taskpaper list (only three currently)
define('ITEM_TASK', 0);
define('ITEM_PROJ', 1);
define('ITEM_LABEL', 2);

// task note types (used in taskpaper.class.php)
define('SINGLE_NOTE', 0);
define('BLOCK_NOTE', 1);

// save_edits types (used in taskpaper.class.php)
define('EDIT_STATE', 0);
define('EDIT_CACHE', 1);
define('EDIT_PLAINTEXT', 2);

// trash and archive tab names
define('FILE_TRASH', '__trash__');
define('FILE_ARCHIVE', '__archive__');

// various request types
define('REQ_INVALID', 0);   // invalid request
define('REQ_INDEX', 1);     // initial index|start page
define('REQ_AJAX', 2);      // via JS event call
define('REQ_URL', 3);       // via browser url|refresh (jquery.address plugin)

// basic app functions, incl. debug and logging functions
require_once(APP_PATH . 'inc/common.php');
require_once(APP_PATH . 'inc/logger.php');

// load the global app config array
$config = array();
require_once(APP_PATH . 'conf/config.php');


// user editable config
// recreated if missing (i.e. new installation)
require_once(APP_PATH . 'inc/ini.class.php');
$ini = new Ini(APP_PATH . 'conf/config.ini', APP_PATH . 'conf/config.new.ini');

// load global language array
// language defaults to en (English) if missing; set in ini file
$langs = glob('./conf/lang_*');
foreach($langs as $lang) {
    $config['lang_list'][] = substr($lang, 12, -4);
}
$cur_lang = $ini->item('language');
$lang_path = 'conf/lang_' . $cur_lang . '.php';
if (!file_exists($lang_path)) {
    $cur_lang = 'en';
    $lang_path = 'conf/lang_' . $cur_lang . '.php';
}
$lang = array();
require_once(APP_PATH . $lang_path);

// used in TaskItem
define('MAX_ACTION', count(lang('state_order')) - 2);

// set correct locale settings (timezone must be set first)
$timezone = $ini->item('timezone');
// @ to avoid error NOTICE if timezone does not exist
if (@date_default_timezone_set($timezone) === false) {
    // this will return a suitable default if user has not set the timezone in his server
    $timezone = date_default_timezone_get();
    date_default_timezone_set($timezone);
}
$location = setlocale(LC_ALL, $cur_lang);
?>