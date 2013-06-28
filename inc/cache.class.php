<?php
namespace tpp\storage;
use tpp, tpp\model;


/**
 * Saves and updates the Content cache.
 */
class Cache {

    private $_parser;
    private $_builder;
    private $_files;
    private $_tabs;


    /**
     * @param Files A Files object, providing access to all taskpaper data files
     */
    function __construct($cache_dir, Files $files) {
        $this->_parser = new Parser;
        $this->_builder = new model\ContentBuilder;
        $this->_files = $files;
        $this->_tabs = new model\Tabs;
        $this->_cache_dir = $cache_dir;
    }


    /**
     * Refresh Taskpaper Content from either Cache or File (newest wins).
     */
    function refresh() {
        $this->_tabs->clear();
        $this->_files->refresh();
        foreach ($this->_files as $file) {
            $content = $this->_refresh_file($file);
            $this->_tabs->add($content);
        }
        $this->_tabs->sort();
        $this->_to_cache(FILE_TAB_CACHE, $this->_tabs);
        return $this;   // chainable
    }


    /**
     * @return Content  Content of Taskpaper name passed in
     */
    function fetch($name) {
        return $this->_from_cache($name);
    }

    function fetch_tabs($force = false) {
        if ($this->_tabs->is_empty() || $force) {
            $this->_tabs = $this->_from_cache(FILE_TAB_CACHE);
        }
        return $this->_tabs;
    }

    /**
     * Saves any taskpaper changes, whether internal or external (i.e. user).
     *
     * Updates cache|file depending on $update_type param.
     *
     * UPDATE_STATE:  no need to rebuild entire cache for simple state changes (done|highlighting) or sorting.
     * UPDATE_PARSED: assume significant parsed items edit.
     *      i.e. tags, dates, added, position changes, etc...,
     *      needs full-rebuild, as other helper arrays need to be updated too!
     * UPDATE_RAWITEMS:  just some of the raw item lines were edited
     * UPDATE_RAW:  entire plain-text string was edited
     * UPDATE_FILE: plain text source file was edited or changed
     *
     * @param Content $content      Content of the Taskpaper updated
     * @param enum    $update_type  Type of update
     * @param string  $raw          Raw edited tasks
     */
    function update(model\Content $content = null, $update_type = UPDATE_NONE, $updates = null) {

        switch ($update_type) {
            case UPDATE_STATE:
                if ($updates !== null) {
                    list($state, $key) = $updates;
                    $content->task_state[$key] = $state;
                }
                $content = $this->_builder->rebuild_from_parsed($content);
                break;
            case UPDATE_PARSED:
                $raw = $this->_builder->parsed_items_to_raw($content);
                $ast = $this->_parser->parse($raw);
                $content = $this->_builder->rebuild($ast, $content);
                break;
            case UPDATE_RAWITEM:
                $raw = $this->_builder->raw_items_to_raw($content);
                $ast = $this->_parser->parse($raw);
                $content = $this->_builder->rebuild($ast, $content);
                break;
            case UPDATE_RAW:
                if ($updates !== null) {
                    $ast = $this->_parser->parse($updates);
                    $content = $this->_builder->rebuild($ast, $content);
                    // also update the tabs (for display updates)
                    $this->_tabs->item($content->name, $content)->sort();
                    $this->_to_cache(FILE_TAB_CACHE, $this->_tabs);
                }
                break;
            case UPDATE_FILE:
                list($name, $file_path) = $updates;
                $raw = file_get_contents($file_path);
                $ast = $this->_parser->parse($raw);
                $content = $this->_builder->build($ast, $name, $file_path);
            default:
        }
        file_put_contents($content->file_path, $content->raw);
        $this->_to_cache($content->name, $content);
        return $content;
    }



        /**
     * Remove any expired cache files (i.e. where source file no longer exists).
     * this is done once a day after a response (in index.php),
     */
    public function cleanup() {
        if ($this->_can_cleanup()) {
            $cache_path = $this->_cache_dir;
            $cache_files = glob($cache_path . "*");
            $cache_files = str_replace(array($cache_path), '', $cache_files);
            $deleted_files = array_diff($cache_files, $this->_files->names());
            foreach ($deleted_files as $deleted_file) {
                unlink($cache_path . $deleted_file);
            }
        }
    }

    /**
     * Totally purge all cached files (mainly for debugging purposes and new installations)
     */
    public function purge() {
        $cache_files = glob($this->_cache_dir . "*");
        foreach ($cache_files as $cache_file) {
            unlink($cache_file);
        }
    }


    /*******************************************/


    private function _to_cache($name, $item) {
        file_put_contents($this->_cache_dir . $name, serialize($item));
    }

    private function _from_cache($name) {
        return unserialize(file_get_contents($this->_cache_dir . $name));
    }

    private function _refresh_file($file) {
        $name = $file->name;
        $file_path = $file->path;
        $cache_path = $this->_cache_dir . $name;

        // confirm the age of the cache
        $file_time = filemtime($file_path);
        if (file_exists($cache_path)) {
            $cache_time = filemtime($cache_path);
        } else {
            $cache_time = 0;
        }

        // which is fresher: cache...
        if ($cache_time >= $file_time) {
            $content = $this->_from_cache($name);
        // ... or file?
        } else {
            $updates = array($name, $file_path);
            $content = $this->update(null, UPDATE_FILE, $updates);
        }
        return $content;
    }


    private function _can_cleanup() {
        // check happens once a day only....
        if (time() > tpp\ini('lastcleanup') + 60 * 60 * 24) {
            // yes this time will be a few milliseconds before the cleanup actually happens, but who cares!
            tpp\ini('lastcleanup', time());
            return true;
        } else {
            return false;
        }
    }
}