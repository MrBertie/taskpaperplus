<?php
namespace tpp\storage;

class Undo {
    const UNDO_FILE = 'undo';

    private $_history = array();

    private $_limit = 10;
    private $_undo_path = '';
    private $_undo_file = '';
    private $_files = null;

    /**
     *
     * @param Files $files   Files class: access to tp+ data files
     */
    function __construct(Files $files) {
        $this->_history_path = config('history_path') . '/';
        $this->_limit = config('undo_limit');
        $this->_files = $files;

        $this->_undo_file = $this->history_path . UNDO_FILE;
        if (file_exists($this->_undo_file)) {
            $this->_history = unserialize(file_get_contents($this->_undo_file));
        }
    }

    function undo($levels = null) {

    }
    /**
     * Save an undo point
     * NOTE: must be called AFTER file changes have been made
     */
    function save() {
        $history_date = array();
        $history_folder = array();
        $prev_modified = $this->_modified();
        $cur_modified = $this->_files->modified_times();

        // just use a unix timestamp as history folder name; simple, unique, sorts
        $folder = $this->_history_path . date('U');
        if ( ! is_dir($folder)) mkdir($folder);

        $new = array_diff_key($cur_modified, $prev_modified);
        $changed = array_diff($cur_modified, $prev_modified);

        $updates = array_merge($new, $changed);
        foreach($updates as $name => $mtime) {
            $copy = $folder . '/' . $name;
            copy($this->_files->fullpath($name), $copy);

            $history_date[$name] = $mtime;
            $history_folder[$name] = $date_tag;
        }

        // deleted files: no need to do anything, they just 'disappear'...
        if ( ! empty($history_date)) {
            $this->_history[$date_tag] = array($history_date, $history_folder);
        }
    }

    private function _modified() {
        $prev = array_pop($this->_history);
        if ( ! empty($prev) && ! is_null($prev)) {
            $modified = $prev['date'];
            return $modified;
        } else {
            return array();
        }
    }
}