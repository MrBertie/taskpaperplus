<?php
namespace tpp\storage;


class FilesCommon {

    protected static $_files = array();
    protected static $_names = array();
    protected static $_archive_file = '';
    protected static $_trash_file = '';
    protected static $_default_file = '';
    protected static $_dir = '';


    function __construct($dir, $default_dir, $default_file) {
        // re-create the default data folder if it is missing (e.g. new install)
        if ( ! file_exists($dir)) {
            $dir = './' . $default_dir;
            mkdir($dir);
        }
        if (substr($dir, -1, 1) != '/') {
            $dir .= '/';
        }
        self::$_dir = $dir;
        self::$_default_file = $default_file;
        self::$_archive_file = FILE_ARCHIVE;
        self::$_trash_file = FILE_TRASH;

        self::refresh();
    }


    protected function _delete(File $file) {

        if ($file !== false && ! $file->restricted()) {

            $timestamp = date('YmdHis') . '-';
            $deletion_path = \tpp\config('deleted_dir');

            if (!file_exists($deletion_path)) {
                mkdir($deletion_path);
            }
            rename($file->path, $deletion_path . $timestamp . $file->name . EXT);

            unset($file);
            self::refresh();

            // return the previous file in the list
            $prev_file = end(self::$_files);
            return $prev_file;
        } else {
            return false;
        }
    }


    protected function _rename(File $file, $name) {
        if ( ! $file->restricted()) {
            $name = self::_validate_name($name);
            if ( ! empty($name)) {
                $new_path = self::_fullpath($name);
                rename($file->path, $new_path);
                $file->name = $name;
                $file->path = $new_path;
                return $this;
            }
        }
        return false;
    }


    function refresh() {

        // create basic files if missing
        self::$_archive_file = $this->_create(FILE_ARCHIVE);
        self::$_trash_file = $this->_create(FILE_TRASH);

        $paths = glob(self::$_dir . "*" . EXT);

        // what if only trash and archive exist?  Create a default task file
        if (count($paths) <= 2) {
            self::_create(self::$_default_file);
            $paths[] = self::$_dir . self::$_default_file . EXT;
        }

        $names = str_replace(array(EXT, self::$_dir), '', $paths);
        self::$_names = $names;

        $modified = array_map(function($path) {
            return filemtime($path);
        }, $paths);

        self::$_files = array();
        $count = count($paths);
        for ($i = 0; $i < $count; $i++) {
            $name = $names[$i];
            if ($name == self::$_archive_file) {
                $type = TAB_ARCHIVE;
            } elseif ($name == self::$_trash_file) {
                $type = TAB_TRASH;
            } else {
                $type = TAB_NORMAL;
            }
            self::$_files[$name] = new File($i, $name, $paths[$i], $modified[$i], $type);
        }

        // Basic initial sort: case insensitive, with symbol first (i.e. '_')
        uksort(self::$_files, 'strcasecmp');
    }


    protected function _exists($name) {
        $exists = file_exists($this->_fullpath($name));
        return $exists;
    }


    protected function _create($name, $sample = null) {
        if (self::_exists($name)) {
            return $name;
        } else {
            $name = self::_validate_name($name);
            if ( ! empty($name)) {
                $path = self::_fullpath($name);
                $text = (is_null($sample)) ? \tpp\lang('new_tab_content') : $sample;
                file_put_contents($path, $text);
                return $name;
            } else {
                return false;
            }
        }
    }


    protected function _validate_name($name) {
        if ( ! self::_restricted($name)) {

            // make sure name is a valid windows file name (more fussy than unix)
            $name = trim(preg_replace('/[^A-Za-z0-9-_ ]/i', '', $name));
            $test_name = ($name == '') ? self::$_default_file : $name;

            // if file already exists then create a new name using a counter at the end until one succeeds
            $counter = 1;
            while (file_exists(self::_fullpath($test_name))) {
               $test_name = $name . $counter;
               $counter++;
            }
            $name = $test_name;
        }
        return $name;
    }


    /**
     * Allow for position index references also
     * @param type $name
     * @return type
     */
    protected function _resolve_index($index) {
        if (is_numeric($index)) {
            $count = count(self::$_names);
            if ($index < 0) {
                $index = $count + $index;
            } elseif ($index >= $count) {
                $index = $count - 1;
            }
            $name = self::$_names[$index];
        }
        return $name;
    }


    protected function _fullpath($name) {
        return self::$_dir . $name . EXT;
    }


    protected function _restricted($name) {
        return ($name == FILE_ARCHIVE || $name == FILE_TRASH);
    }
}



/**
 * Handles all access and management of Taskpaper files
 *
 * @author Symon Bent
 */
class Files extends FilesCommon implements \IteratorAggregate {

    function __construct($dir, $default_dir, $default_file) {
        parent::__construct($dir, $default_dir, $default_file);
    }


    function __invoke($name) {
        return $this->item($name);
    }


    function index($index) {
        $name = parent::_resolve_name($index);
        return self::$_files[$name];
    }

    function item($name) {
        if (in_array($name, self::$_names)) {
            return self::$_files[$name];
        } else {
            return false;
        }
    }


    function exists($name) {
        return parent::_exists($name);
    }


    function create($name, $sample = null) {
        return parent::_create($name, $sample);
    }


    function delete($name) {
        $file = $this->item($name);
        return parent::_delete($file);
    }


    function rename($old, $new) {
        $file = $this->item($old);

        if ($file !== false) {
            return parent::_rename($file, $new);
        }
    }

    function count() {
        return count(self::$_files);
    }

    function first() {
        if ($this->count() < 2) {
            parent::refresh();
        }
        return end(self::$_files);
    }

    function names() {
        return self::$_names;
    }

    function trash_file() {
        return self::$_files[self::$_trash_file];
    }

    function archive_file() {
        return self::$_files[self::$_archive_file];
    }

    function getIterator() {
        return new \ArrayIterator(self::$_files);
    }
}


class File extends FilesCommon {

    public $index, $name, $path, $modified, $type;

    function __construct($index, $name, $path, $modified, $type) {
        $this->index = $index;
        $this->name = $name;
        $this->path = $path;
        $this->modified = $modified;
        $this->type = $type;
    }

    function restricted() {
        return ($this->type != TAB_NORMAL);
    }

    function delete() {
        $result = parent::_delete($this);
        if ($result !== false) {
            unset ($this);
        }
        return $result;
    }

    function rename($name) {
        return parent::_rename($this, $name);
    }

    function refresh() {
        self::refresh();
        return $this;
    }
}