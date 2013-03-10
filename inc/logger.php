<?php

define('LOGGER_DEBUG', 0);
define('LOGGER_LOG', 1);

function bug() {
    logger(LOGGER_DEBUG, func_get_args());
}

function msg() {
    logger(LOGGER_LOG, func_get_args());
}
/**
 * Simple, practical and fast logging function:
 * Usage:
 * 1. de&&bug("your message here", $variable, $array, $object, $etc);
 * 2. log&&msg("as above");
 *
 * The two types: debug and logmsg exist so that you can keep your log separate from debug calls
 *
 * To turn logging|debug on|off:
 * - create a constant called 'de' or 'log' and set true|false
 * e.g. define ('de', true); // set debugging on
 *
 * @staticvar file $log_file => cached log file
 * @staticvar int $start => first time function was called during this request
 * @param enum $debug LOGGER_DEBUG or LOGGER_MSG
 * @param [multiple] $message => any number of variables|strings, to be displayed
 */
function logger() {
    global $config;

    static $files = array();
    static $start = 0;
    static $paths = array();


    if (empty($paths)) {
        $paths = array($config['debug_file'], $config['log_file']);
    }

    $print_header = ($start == 0);
    if ($start == 0) {
        $start = microtime(true);
        $time = 0;
    } else {
        $time = microtime(true) - $start;
    }
    $time = sprintf('%0.06f ms', $time);

    $args = func_get_args();

    $debug = $args[0];
    if ($debug == LOGGER_LOG) {
        $line = str_repeat('.', 40) . "\n";
        $indent = "\n\t\t\t\t";
    } else {
        $line = '';
        $indent = "\n";
    }

    array_shift($args);

    if( ! empty($args)) {

        if( ! isset($files[$debug])) {
            $file_path = \APP_PATH . $paths[$debug];
            $open_type = 'a';
            $files[$debug] = fopen($file_path, $open_type) or exit("Cannot open Log file: ".$file_path);
        }

        if ($print_header) {
            fwrite($files[$debug], "\n\nLog File:  " . date('Y-m-d H:i:s') . "\n" .
                    str_repeat('=', 40) . "\n");
        }

        for ($i = 0; $i < count($args[0]); $i++) {
            $items[] = var_export($args[0][$i], true);
        }

        $text = (string) ($items !== null && is_array($items) ? implode($indent, $items) : $items);
        fwrite($files[$debug], '[' . $time . ']' . "\t" . $text . "\n" . $line);
    }
}