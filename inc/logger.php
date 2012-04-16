<?php

function bug($message) {
    logger(true, func_get_args());
}

function msg($message) {
    logger(false, func_get_args());
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
 * @param bool $debug => true if debug
 * @param [multiple] $message => any number of variables|strings, to be displayed
 */
function logger($debug, $message) {
    static $log_file;
    static $start = 0;

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
    array_shift($args);

    if(!empty($args)) {
        if(!$log_file) {
            $file_path = APP_PATH . ($debug) ? config('debug_file') : config('log_file');
            $open_type = 'a';
            $log_file = fopen($file_path, $open_type) or exit("Cannot open Log file: ".$file_path);
        }
        if ($print_header) {
            fwrite($log_file, "\n\nLog File:  " . date('Y-m-d H:i:s') . "\n" .
                    str_repeat('=', 30) . "\n");
        }
        for ($i = 0; $i < count($args); $i++) {
            $args[$i] = var_export($args[$i], true);
        }
        $message = ($args !== null && is_array($args)) ? implode("\t", $args) : $args;
        fwrite($log_file, '[' . $time . ']' . "\t" . $message . "\n");
    }
}