<?php

/**
 * Handles all access and management of files
 *
 * @author syanna
 */
class Files {

    private $_items = array();    // all available taskpapers in data directory
    private $_archive_file = '';
    private $_folder = '';

    /**
     *
     * @param string $taskpaper_folder  = location of taskpaper files (from server root)
     * @param string $archive_file      = name of archive file (no extension)
     *
     */
    function __construct($taskpaper_folder) {

        // re-create the default data folder if it is missing (could be new install)
        if (!file_exists($taskpaper_folder)) {
            $taskpaper_folder = './' . config('data_folder');
            mkdir($taskpaper_folder);
        }
        if (substr($taskpaper_folder, -1, 1) != '/') {
            $taskpaper_folder .= '/';
        }
        $this->_folder = $taskpaper_folder;
        // default archive & trash taskpaper files will be recreated if missing
        $this->_refresh_items();
        $this->_archive_file = $this->create(FILE_ARCHIVE);
        $this->_trash_file = $this->create(FILE_TRASH);
        // ensure that there is at least one usable taskpaper available, if only archive & trash exist
        if ($this->count() == 2) $this->create(config('default_active'), true);
        $this->_refresh_items();
    }
    function exists($name_or_idx) {
        $exists = false;
        if (is_numeric($name_or_idx) && $name_or_idx < $this->count()) {
            $exists = true;
        } elseif (array_search($name_or_idx, $this->_items) !== false) {
            $exists = true;
        }
        return $exists;
    }
    function resolve_name($name_or_idx) {
        $name = false;
        if ($this->exists($name_or_idx)) {
            if (is_numeric($name_or_idx)) {
                $name = $this->_items[$name_or_idx];
            } else {
                $name = $name_or_idx;
            }
        }
        return $name;
    }
    // returns first tab, not including archive|trash
    function first() {
        if (count($this->_items) > 2) {
            $first = $this->_items[2];
        } else {
            $first = $this->create(config('default_active'));
        }
        return $first;
    }
    /**
     * Removes the specific taskpaper file to the _deleted folder
     */
    function delete($name_or_idx) {
        $name = $this->resolve_name($name_or_idx);
        if ($name !== false && $name != $this->_archive_file && $name != $this->_trash_file) {
            $idx = array_search($name, $this->_items);
            $timestamp = date('YmdHis') . '-';
            $deletion_path = config('deleted_path');
            if (!file_exists($deletion_path)) {
                mkdir($deletion_path);
            }
            rename($this->_fullpath($name), $deletion_path . $timestamp . $name . EXT);
            $this->_refresh_items();
            // return te name of a suitable replacement...
            return $this->item($idx);
        } else {
            return false;
        }
    }
    /**
     * Renames the specific taskpaper file
     * returns the name that was actually used (sanitised where necessary)
     */
    function rename($old, $new) {
        $old = $this->resolve_name($old);
        $new = $this->_get_valid_name($new);
        if (!($old === false && empty($new))) {
            $old_name = $this->_fullpath($old);
            $new_name = $this->_fullpath($new);
            rename($old_name, $new_name);
            $this->_refresh_items();
            return $new;
        } else {
            return false;
        }
    }
    /**
     * Creates a new taskpaper file
     * @param <string> $name name of new taskpaper file
     * @return name of new file, original if it already exists, or false name was empty|useless
     */
    function create($name, $show_sample = false) {
        if ($this->exists($name)) {
            return $name;
        } else {
            $name = $this->_get_valid_name($name);
            if (!empty($name)) {
                $path = $this->_fullpath($name);
                $sample = ($show_sample) ? lang('msg_new_content') : '';
                file_put_contents($path, $sample);
                $this->_refresh_items();
                return $name;
            } else {
                return false;
            }
        }
    }
    /**
     * return full path of a specific taskpaper file
     */
    function fullpath($name_or_idx) {
        $name = $this->resolve_name($name_or_idx);
        if ($name !== false) {
            return $this->_fullpath($name);
        } else {
            return false;
        }
    }
    function count() {
        return count($this->_items);
    }
    function archive_path() {
        return $this->_fullpath($this->_archive_file);
    }
    function trash_path() {
        return $this->_fullpath($this->_trash_file);
    }
    /**
     * List of all available taskpaper text files
     * without extensions; or a specific item
     *
     * @return <array of strings> list of names
     */
    function items() {
        return $this->_items;
    }
    function item($idx) {
        if ($idx < $this->count()) {
            return $this->_items[$idx];
        } else {
            return false;
        }
    }
    function last_modified() {
        return $this->_last_modified;
    }
    /**
     * remove any expired cache files (i.e. where source file no longer exists)
     * this is done once a day after a response (in Dispatcher class),
     */
    public function cleanup_cache() {
        if ($this->_can_cleanup_cache()) {
            $cache_path = config('cache_path');
            $cache_files = glob($cache_path . "*");
            $cache_files = str_replace(array($cache_path), '', $cache_files);
            $deleted_files = array_diff($cache_files, $this->_items);
            foreach ($deleted_files as $deleted_file) {
                unlink($cache_path . $deleted_file);
            }
        }
    }
    /**
     * Totally purge all cached files (mainly for debugging purposes and new installations)
     */
    public function purge_cache() {
        $cache_path = config('cache_path');
        $cache_files = glob($cache_path . "*");
        foreach ($cache_files as $cache_file) {
            unlink($cache_file);
        }
    }

    // ************************
    private function _can_cleanup_cache() {
        global $ini;
        // check happens once a day only....
        if (time() > $ini->item('lastcleanup') + 60 * 60 * 24) {
            // yes it will do a few milliseconds before the cleanup
            // actually happens, but who cares!
            $ini->item('lastcleanup', time())->save();
            return true;
        } else {
            return false;
        }
    }
    /**
     * obtains a list if all taskpaper files in the data folder
     * without path or extension
     */
    private function _refresh_items () {
        $files = glob($this->_folder . "*" . EXT);
        $this->_items = str_replace(array(EXT, $this->_folder), '', $files);
        $this->_last_modified = array();
        foreach($files as $file) {
            list($key, $name) = each($this->_items);
            $this->_last_modified[$name] = filemtime($file);
        }
        usort($this->_items, 'strcasecmp'); // sort case insensitive, with symbol first (i.e. '_')
    }
    private function _get_valid_name($name) {
        if ($name != FILE_ARCHIVE && $name != FILE_TRASH) {
            // make sure name is a valid windows file name (more fussy than unix)
            $name = trim(preg_replace('/[^A-Za-z0-9-_ ]/i', '', $name));
            $test_name = ($name == '') ? config('default_active') : $name;
            $counter = 1;
            while (file_exists($this->_fullpath($test_name))) {
               $test_name = $name . $counter;
               $counter++;
            }
            $name = $test_name;
        }
        return $name;
    }
    private function _fullpath($file_name) {
        return $this->_folder . $file_name . EXT;
    }
}
?>
