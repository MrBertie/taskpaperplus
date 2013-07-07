<?php
namespace tpp\storage;

/**
 * Simple file to access config data from a standard INI file
 * @param load => parse the INI file into an array
 * @param save => update the INI file based on changed items only
 * @param item => get/set the specified item
 * @param changed => true if item values have changed
 *
 * @author sbent
 */

class Ini implements \Iterator {
    private $_ini_file = '';
    private $_ini_items = array();
    private $_changed_keys = array();
    private $_changed_values = array();

    function __construct($filename, $default_filename = '', $upgrade = true) {
        if (!file_exists($filename)) {
            if (file_exists($default_filename)) {
                copy($default_filename, $filename);
            } else {
                trigger_error("The INI settings file is missing: $filename");
                die;
            }
        }
        $this->_ini_file = $filename;
        $this->load();
        // check for updated ini settings if necessary
        // IF cong/upgrade exists!
        if ($upgrade && file_exists(APP_PATH . 'conf/upgrade')) {
            $new_ini = new Ini($default_filename, '', false);
            $new_ini->load();
            foreach($new_ini as $key => $value) {
                if ($this->item($key) === null) {
                    $this->item($key, $value);
                }
            }
            $this->save();
            unset($new_ini);
            unlink(APP_PATH . 'conf/upgrade');
        }
    }
    function load() {
        $this->_ini_items = parse_ini_file($this->_ini_file);
        return $this;
    }
    function item($key, $value = NULL) {
        if (!empty($value)) {
            if (array_key_exists($key, $this->_ini_items)) {
                $this->_changed_keys[] = '/(' . $key . '=).+$/';
                $this->_changed_values[] = "\${1}" . $value; // ${1} style to avoid problems with leading digits
            } else {
                $this->_new_items = "\n" . $key . '=' . $value;
            }
            $this->_ini_items[$key] = $value;
            return $this;
        } else {
            return $this->_ini_items[$key];
        }
    }
    function save() {
        if ($this->changed()) {
            $ini_text = file_get_contents($this->_ini_file);
            // replace only changed lines
            $ini_text = preg_replace(array_values($this->_changed_keys), array_values($this->_changed_values), $ini_text);
            file_put_contents($this->_ini_file, $ini_text);
            // reset all changes
            unset($this->_changed_items);
        } elseif ($this->added()) {
            $ini_text = rtrim(file_get_contents($this->_ini_file), "\n");
            $ini_text .= $this->_new_items;
            file_put_contents($this->_ini_file, $ini_text);
            unset($this->_new_items);
        }
        return $this;
    }
    function changed() {
        if (count($this->_changed_keys) > 0) {
            return true;
        } else {
            return false;
        }
    }
    function added() {
        if (count($this->_new_items) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Iterator interface
    function rewind() {
        reset($this->_ini_items);
    }
    function current() {
        return current($this->_ini_items);
    }
    function key() {
        return key($this->_ini_items);
    }
    function next() {
        next($this->_ini_items);
    }
    function valid() {
        return current($this->_ini_items) !== false;
    }

}