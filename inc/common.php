<?php
namespace tpp;
use tpp\storage;

/**
 * Convenience function to access all [localised] language strings.
 *
 * All localised lang strings pass through here.
 * Set ['hide_tips'] to make the pop up tips disappear
 *
 * @global array $config    Configuration file
 * @global array $lang      Localised language strings
 * @param string $item      Requested $lang item
 * @return string
 */
function lang($item) {
    global $config, $lang;
    if ($config['hide_tips'] === true && strpos($item, '_tip') > 0) {
        return '';
    }
    return $lang[$item];
}
/**
 * Convenience function to access all fixed/internal config values.
 *
 * @global array $config    Configuration file
 * @param string $item
 * @return string
 */
function config($item) {
    global $config;
    return $config[$item];
}
/**
 * Convenience function to access user editable config values.
 *  config, recreated if missing (e.g. new installation).
 *
 * @staticvar Ini $ini  Singleton ini instance
 * @param string $item
 * @param string $value
 * @return string
 */
//
function ini($item, $value = null) {
    static $ini = null;
    if (is_null($ini)) {
        $ini = new storage\Ini(APP_PATH . 'conf/config.ini', APP_PATH . 'conf/config.new.ini');
    }
    if (is_null($value)) {
        return $ini->item($item);
    } else {
        $ini->item($item, $value);
        $ini->save();
    }
}

function term($item) {
    global $term;
    return $term[$item];
}
/**
 * Useful for POST|GET variables.
 */
function isset_or(&$var, $alt) {
    return (isset($var) ? $var : $alt);
}
/**
 * @return Long date, will be localised to current language
 */
function long_date() {
    $date = strftime("%A, %B %#d, %Y");
    return iconv('ISO-8859-1', 'UTF-8', $date);
}
/**
 * Convenience function to remove underscores
 */
function no_underscores($text) {
    return str_replace('_', ' ', $text);
}

/**
 * Inserts the $insert array into the $array at the $key position (assoc|numeric key).
 *
 * @param array $array  The target array to insert into
 * @param numeric|string $key   The location to insert (0-based like arrays)
 * @param array $insert The array to be inserted.
 * @param bool $overwrite   if true: over write existing items (up to length of $insert array only!)
 * @return array
 */
function array_insert($array, $key, $insert, $offset = 0, $overwrite = false) {
  $index = array_search($key, array_keys($array));
  if ($index === FALSE) {
      $index = count($array); // insert at end of array if $key not found
  }
  $skipped = ($overwrite) ? count($insert) : 0;
  $begin = array_slice($array, 0, $index + $offset);
  $end = array_slice($array, $index + $offset + $skipped);
  return array_merge($begin, $insert, $end);
}