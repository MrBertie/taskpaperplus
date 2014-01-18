<?php
/**
 * The Taskaper Application.
 *
 * This is the main user API.
 */
namespace tpp\model;
use tpp\storage;


/**
 * Models the main Taskpaper Application.
 *
 * Main part of API used by the dispatcher and templates to work with the active taskpaper: to edit, delete, search, etc...
 *
 * Creates default taskpapers for Archive, Trash, and a default Tab if config is wrong or missing.
 * Provides access to the currently active Taskpaper and the archive path.
 * 

 */
class Taskpapers {

    private $_active_tpp    = null;  // active Taskpaper instance
    private $_active_file   = null;  // active File instance
    private $_tabs          = null;  // list of current tabs
    private $_files;
    private $_cache;


    /**
     * @param $active_tab,
     * @param storage\Files $files
     * @param storage\Cache $cache
     */
    function __construct($active_tab, storage\Files $files, storage\Cache $cache) {
        $this->_files = $files;
        $this->_cache = $cache;
        $this->_cache->refresh();
        $this->_tabs = $this->_cache->fetch_tabs();
        $this->set($active_tab);
    }


    function __invoke($name = null) {
        if ( ! is_null($name)) {
            return $this->item($name);
        } else {
            return $this->active();
        }
    }


    function refresh() {
        $this->_cache->refresh();
    }


    /**
     * Get active taskpaper instance, or specific one if a name is provided.
     *
     * If active is missing then first available [unrestricted] tab is returned.
     * If named then the real file must exist, not just the cached version.
     *
     * @param string $name  name of a different taskpaper..
     * @return object|false   returns the current or named taskpaper instance; false if named does not exist.
     */
    function get($name = null) {

        // if no name and active tpp exists then return the active tab
        if (is_null($name)) {
            if (! is_null($this->_active_file) &&
                $this->exists($this->_active_file->name)) {
                $tpp = $this->_active_tpp;
            } else {
                $tpp = $this->_set('');
            }
        } else {
            $tpp = $this->_set($name, false);
        }
        return $tpp;
    }
    
    /**
     * Set currently active taskpaper instance.
     * 
     * @param string $name
     * @return Taskpaper|boolean
     */
    function set($name) {
        $tpp = $this->_set($name, true);
        \log&&msg('Set active taskpaper to: ', $tpp->name());
        return $tpp;
    }
    
    
    /**
     * If active tpp does not exist default to the first available tab.
     * 
     * @param string $id
     * @param boolean $set_cur
     * @return boolean 
     */
    private function _set($name = null, $set = false) {
        if (empty($name) || ! $this->exists($name)) {
            $file = $this->_files->first();
            $name = $file->name;
            $set  = true;
        } else {
            $file = $this->_files->item($name);
        }
        $tpp = $this->_get_taskpaper($name);
        
        if ($set || is_null($this->_active_file)) {
            $this->_active_file = $file;
            $this->_active_tpp = $tpp;
        }
        // tab changed, so update tab list
        $this->_tabs = $this->_cache->fetch_tabs(true);
        return $tpp;
    }
    

    /**
     * Creates a new taskpaper.
     *
     * IMPORTANT: does not reset the active tpp; call set($name) to activate the new taskpaper
     *
     * @param string    $name proposed name of new taskpaper file; could have been sanitised (i.e. changed), hence the reason why the active tpp is not automatically changed.
     * @return string|bool   approved taskpaper name or false on failure
     */
    function create($name, $text = null) {
        $name = $this->_files->create($name, $text);
        if ($name !== false) {
            $this->_cache->refresh();

            \log&&msg("created a new taskpaper called: $name");

        }
        return $name;
    }
    
    
    function exists($name) {
        return $this->_files->exists($name);
    }

    
    /**
     * Removes the active taskpaper file to the _deleted folder or specific taskpaper file if $name is provided.
     *
     * @return boolean success|faliure
     */
    function delete($name = null) {
        $name = (is_null($name)) ? $this->_active_file->name : $name;
        $next_file = $this->_files->delete($name);
        if ( ! $next_file == false) {
            $this->_cache->refresh();
            $this->set($next_file->name);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Renames the ACTIVE taskpaper file.  Avoid using, better to change Display name instead.
     *
     * @return string the new name (may be santitised!)
     */
    function rename($name) {
        $file = $this->_active_file->rename($name);
        // TODO: rename is not working
        if ($file !== false) {
            $this->_cache->refresh();
            $this->_set($file->name);
            return $file->name;
        } else {
            return false;
        }
    }

    /**
     * Return an array of positions, names, display names, and trash/archive identity
     *
     * @return array of StdClass objects
     */
    function tabs() {
        return $this->_tabs;
    }

    function count() {
        return $this->_files->count();
    }

    // needed by TaskItems in order to archive items
    function archive_path() {
        return $this->_files->archive_file()->path;
    }

    function trash_path() {
        return $this->_files->trash_file()->path;
    }

    private function _get_taskpaper($name) {
        return new Taskpaper($this->_cache, $name,
                                $this->trash_path(), $this->archive_path());
    }
}

 /** 
  * This class is completely STATIC and allows the $content to be shared and changed by all implementing classes.
  */
class TaskpaperPersist {

    protected static $_content;
    protected static $_cache;
    protected static $_name;

    static function init(storage\Cache $cache, $name) {
        self::$_cache   = $cache;
        self::$_name    = $name;
        self::$_content = $cache->fetch($name);
    }

    /**
     * Refreshes (or syncs) the text file and cache (session) data based on last modified times
     *
     * @see Cache->refresh()
     */
    static function refresh() {
        self::$_content = self::$_cache->refresh()->fetch(self::$_name);
    }

    /**
     * Saves the user's edits/changes to the taskpaper text file.
     *
     * This could be:
     * UPDATE_RAW: direct edits, i.e. by passing in the changes plain text string
     * UPDATE_PARSED:  where for example a line was deleted, edited, changed
     *              In this case 'update' uses the current in-memory Content to recreate the
     *              edited tasks)
     * UPDATE_STATE: where only state was changed (state highlighting, completed)
     *             only the text file needs to be updated, cache is updated in-place, and
     *             doesn't need to be rebuilt (avoids an expensive process)
     *
     * @param String $raw   plain text string of tasks
     */
    static function update($edit_type, $updates = null) {
        self::$_content = self::$_cache->update(self::$_content, $edit_type, $updates);
    }

    static function is_restricted() {
        return (self::$_content->tab_type != TAB_NORMAL);
    }

    static function raw_item($key, $text = null) {
        if (is_null($text)) {
            return self::$_content->raw_items[$key];
        } else {
            self::$_content->raw_items[$key] = $text;
            self::update(UPDATE_RAWITEM);
        }
    }
}



/**
 * Models a single taskpaper, usually the currently active one.
 * 
 * Constructs arrays from the taskpaper text file (or session array if unchanged).
 * Includes tasks, tags, projects, and various indexes needed for filtering and searching
 * All taskpaper data is build by and stored in a Content class.
 *
 * Provides methods to add, remove, edit and search, and access the tag and
 * project lists. Plus to save and update the tasks
 *
 * NOTE: taskpaper has no notion of state; that is left to the State objects.
 * It is concerned with content and manipulating it.
 */
class Taskpaper extends TaskpaperPersist {

    private $_trash_path = '';
    private $_archive_path = '';

    /**
     * @param storage\cache $cache
     */
    function __construct(storage\Cache $cache, $name, $trash_path, $archive_path) {
        parent::init($cache, $name);

        $this->_archive_path = $archive_path;
        $this->_trash_path   = $trash_path;
    }
    

    /**
     * Get the Taskpaper name.
     */
    function name() {
        return self::$_name;
    }

    /**
     * Item OR List of all items, in raw text order: Page, Project, Task, Info.
     * 
     * @param string $key   If null return array list of all items, otherwise return specific item refered to by key.
     *
     * @return [...]Item | Items    specific item OR array/iterator of ALL Item types
     */
    function items($key = null) {
        $items = new Items();
        if ($key === null) {
            return $items;
        } else {
            return $items[$key];
        }
    }

    /**
     * Adds a new task into the curent taskpaper.
     *
     * This is used when adding new tasks from the input box.
     *
     * @param string $new_task      The new task as text, as typed by the user, can include notes, and a project identifier ('/2')
     * @param int    $project_num The project nnumber (not index!) into which to insert
     * @return boolean  True on success
     */
    function add($new_task, $project_num = 0) {
        $max = self::$_content->project_count - 1;
        $project_num = ($project_num > $max) ? $max : $project_num;
        $at_top = \tpp\ini('insert_pos') == 'top';

        // edge case: insert at end of list only 1 project exists or last project
        if (($max == 0 || $project_num == $max) && ! $at_top) {   
            $raw = $this->raw() . "\n" . $new_task;
            self::update(UPDATE_RAW, $raw);
            return true;
            
        // edge case: at top of orphan project (0)
        } elseif ($project_num == 0 && $at_top) {
            $this->replace('010', $new_task);
            return true;
            
        // or insert into a specific Project
        } elseif ($project_num >= 0) {
            if ($project_num > $max) {
                $project_num = $max;
            }
            if ($at_top) {
                // find this project's index
                $proj = array_search($project_num, self::$_content->project_index);
                // then find the key of the next (task) item
                $keys = array_keys(self::$_content->raw_items);
                $key = $keys[array_search($proj, $keys) + 1];
            } else {
                // just find the next project's index
                $key = array_search($project_num + 1, self::$_content->project_index);
            }
            $this->replace($key, $new_task);
            return true;
        }
        return false;
    }

    function replace($key, $text) {
        $new_item = array('new' => $text);
        self::$_content->raw_items = \tpp\array_insert(self::$_content->raw_items, $key, $new_item);
        self::update(UPDATE_RAWITEM);
    }

    function trash($key) {
        // Can't 'delete' from the 'Trash bin' Taskaper
        if (self::$_content->tab_type != TAB_TRASH) {
            $this->_copy_to($key, $this->_trash_path);
        }
        $this->_remove($key);
    }

    function trash_done() {
        $this->_remove_done($this->_trash_path);
    }

    function archive($key) {
        $this->_copy_to($key, $this->_archive_path);
        $this->_remove($key);
    }

    function archive_done() {
        $this->_remove_done($this->_archive_path);
    }

    function remove_actions() {
        global $term;
        // crude but effective way to remove the ** symbols at the end of lines...
        $raw = preg_replace($term['action'], "$2", $this->raw());
        self::update(UPDATE_RAW, $raw);
    }

    /**
     * Returns the raw content of the current taskpaper text file.
     *
     * @return String   plain text file content
     */
    function raw() {
        return self::$_content->raw;
    }

    /**
     * Returns an array of ALL tags.
     *
     * @return  Array of strings
     */
    function tags() {
        return self::$_content->tags;
    }

    /**
     * Returns a list of ALL projects.
     *
     * @return Array of parsed Projects
     */
    function projects() {
        return new ProjectItems();
    }

    /**
     * Return a project's index number based on its item_key
     *
     * @param $key A Project index key
     * @return integer
     */
    function project_index($key) {
        return self::$_content->project_index[$key];
    }

    function reorder_tasks($new_order, $project = null) {
        self::$_content->reorder_tasks($new_order, $project);
        self::update(UPDATE_PARSED);
    }


    function reorder_projects($new_order) {
        self::$_content->reorder_projects($new_order);
        self::update(UPDATE_PARSED);
    }

    /**
     * Returns a new Task Search object which provides methods to search by: command, tag, project, filter, expression (incl. date or time period).
     *
     * @return object Search
     */
    function search() {
        return new Search($this, self::$_content);
    }

    
    function restricted() {
        return parent::is_restricted();
    }


    private function _remove_done($target_path) {
        global $term;
        $keys = preg_grep('/^' . $term['done_prefix'] . $term['task_prefix'] . '.*/', self::$_content->raw_items);
        if ( ! empty($keys)) {
            $keys = array_keys($keys);
            $this->_copy_to($keys, $target_path);
            $this->_remove($keys);
        }
    }

    /**
     * Copy this task to the archiving|trash taskpaper.
     */
    private function _copy_to($keys, $target_path) {
        global $term;
        if ( ! is_array($keys)) {
            $keys = array($keys);
        }
        $tasks = '';
        foreach ($keys as $key) {
            $tab = self::$_content->name;
            $task = self::$_content->parsed_items[$key];
            $project = $task->project_name;
            $notes = ($task->note->len > 0) ? $task->note->text . "\n" : '';
            $tasks .= $task->raw . "\n";
            $tasks .= $term['note_prefix'] . "\n";
            $tasks .= \tpp\lang('deleted_lbl') . " "
                    . date("d-M-Y H:i")
                    . " | " . $tab
                    . " | " . $project . "\n";
            $tasks .= $notes;
            $tasks .= $term['note_prefix'] . "\n";
        }
        // this seems unwieldy but I can't find a better way to append to the top!
        $text = file_get_contents($target_path);
        $text = $tasks . $text;
        file_put_contents($target_path, $text);
    }

    private function _remove($keys) {
        if ( ! is_array($keys)) {
            $keys = array($keys);
        }
        foreach ($keys as $key) {
            unset(self::$_content->raw_items[$key]);
        }
        self::update(UPDATE_RAWITEM);
    }
}



class BasicItems extends TaskpaperPersist implements \ArrayAccess, \Iterator {

    protected $_items = array();

    function __construct() {
        $this->_items = &self::$_content->parsed_items;
    }

    function item($key) {
        // returns the specified task item as an (...)Item object
        $ns = '\\tpp\\model\\';
        $class = $ns . ucfirst(self::$_content->types[$key]) . 'Item';
        if ( ! class_exists($class)) {
            $class = $ns . 'BasicItem';
        }
        $item = new $class(self::$_content->parsed_items[$key]);
        return $item;
    }

    function count() {}

    function project_count() {}

    function tag_count() {}


    // *** Iterator interface ***
    function rewind() {
        reset($this->_items);
    }

    function current() {
        if ($this->valid()) {
            return $this->item($this->key());
        } else {
            return false;
        }
    }

    function key() {
        return key($this->_items);
    }

    function next() {
        next($this->_items);
    }

    function valid() {
        return current($this->_items) !== false;
    }

    // *** ArrayAccess interface ***
    public function offsetSet($offset, $key) {
        $this->_items[$offset] = $this->item($key);
    }

    public function offsetExists($offset) {
        return $this->_exists($offset);
    }

    public function offsetUnset($offset) {
        unset($this->_items[$offset]);
    }

    public function offsetGet($offset) {
        return $this->_exists($offset) ? $this->item($offset) : null;
    }

    private function _exists($key) {
        return isset($this->_items[$key]);
    }
}


class Items extends BasicItems {
    
    function __construct() {
        parent::__construct();
    }

    function count() {
        return self::$_content->task_count;
    }
    function project_count() {
        return self::$_content->project_count;
    }
    function tag_count() {
        return self::$_content->tag_count;
    }
}


class ProjectItems extends BasicItems {

    function __construct() {
        $this->_items = &self::$_content->projects;
        $this->_count = &self::$_content->project_count;
    }

    function item($index) {
        $project = self::$_content->project_by_index($index);
        return new ProjectItem($project);
    }

    function count() {
        return $this->_count;
    }
    function project_count() {
        return $this->_count;
    }
    function tag_count() {
        return 0;
    }
}


/**
 * Result of a Search query: any matching Projects | Tasks | Info lines, based on the filter expression.
 *
 * Result of a search.
 *
 * title = filter used
 * Separated into Projects and Tasks
 *
 * Accessible as array/iterator
 */
class FilteredItems extends Items {

    private $_projects = array();
    private $_count = 0;
    private $_title = '';
    private $_groups = array();

    /**
     * @param array $items
     * @param integer $task_count
     * @param array $projects
     * @param integer $project_count
     * @param string $title usually the filter used to produce this
     */
    function __construct(Array $items,
                         $count = 0,
                         Array $projects = array(),
                         $project_count = 0,
                         $title = '',
                         Array $groups = array()
                         ) {
        // filtered raw_items array: only the $keys are actually used
        $this->_items = $items;
        $this->_projects = new FilteredProjects($projects, $project_count);
        $this->_count = $count;
        $this->_title = $title;
        $this->_groups = $groups;
    }

    function projects() {
        return $this->_projects;
    }

    function count() {
        return $this->_count;
    }

    function title() {
        return $this->_title;
    }

    private function _info($text) {
        $info = (object) array('type' => 'info', 'text' => $text, 'raw' => $text, 'note' => array('text' => "", 'len' => 0, 'raw' => ""));
        return $info;
    }
}


class FilteredProjects extends ProjectItems {

    function __construct(
        Array $projects = array(),
        $project_count = 0) {
        $this->_items = $projects;
        $this->_count = $project_count;
    }

    function item($key) {
        $project = self::$_content->project_by_key($key);
        return new ProjectItem($project);
    }
}



/**
 * Glorified wrapper around a parsed_item
 */
class BasicItem extends TaskpaperPersist {

    protected $_parsed;
    protected $_hidden;

    /**
     * @param \tpp\model\Taskpaper $parent  Used to update the Content when changes happen
     * @param object $parsed    Reference to a parsed item; to allow editing
     */
    function __construct(\StdClass &$parsed) {
        $this->_hidden = ($parsed->type == 'project' && $parsed->index == 0);
        $this->_parsed = &$parsed;
    }

    function key() {
        return $this->_parsed->key;
    }

    function type() {
        return $this->_parsed->type;
    }

    /**
     * Set/get the item text.
     *
     * @param type $value The new text
     * @return string The current text
     */
    function text() {
        return $this->_parsed->text;
    }

    /**
     * The note object: text, len (line count), raw (incl. prefix lines).
     *
     * @return object note
     */
    function note() {
        return $this->_parsed->note;
    }

    /**
     * Set/get the plain text value of the item.
     *
     * Includes all notes, done, star, tags; the whole thing as in a plain text file.
     * Used for edit boxes.
     *
     * @param string $value new plain text of item
     * @return string
     */
    function raw($value = null) {
        $key = $this->key();
        return parent::raw_item($key, $value);
    }

    function hidden() {
        return $this->_hidden;
    }
}


/**
 * A neutral info line in the task list
 */
class InfoItem extends BasicItem {

}


/**
 * A project line in the task list
 */
class ProjectItem extends BasicItem {
    
    function empty_orphans() {
        return $this->_parsed->index == 0 && empty($this->_parsed->children);
    }

    function numbered_text() {
        return $this->index() . ' ' . $this->_parsed->text;
    }

    function index() {
        return $this->_parsed->index;
    }
}


/**
 * Represents a task line, parsed into 'done, text, tags, state and notes'.
 *
 * All changes are made directly to the cache arrays; which is then updated accordingly.
 * Can be set by element or as a whole via ->plain().
 * Can update itself.
 */
class TaskItem extends BasicItem {

    /**
     * Set/get task "Done" state.
     *
     * Signified by the presence|absence of the X at beginning of the task.
     * (see config.php to change the syntax)
     *
     * Current state highlighting will be restored should the task be "undone".
     *
     * @param boolean $value The new "Done" state:  true/false or 'swap' to invert the current setting
     * @return boolean The current "Done" state
     */
    function done($value = null) {
        if (is_null($value)) {
            return $this->_parsed->done;
        } else {
            if ($value == 'swap') {
                $this->_parsed->done = ! $this->_parsed->done;
            } else {
                $this->_parsed->done = (bool) $value;
            }
            $updates = $this->_to_state($this->_parsed);
            $this->_update(UPDATE_STATE, $updates);
            return $this;
        }
    }

    /**
     * Set/get the action , i.e. highlighting: next, wait, maybe, etc...
     *
     * @param null | integer $action
     * @return integer | TaskItem
     */
    function action($action = null) {
        if (is_null($action)) {
            return $this->_parsed->action;
        } else {
            if ($action < 0) {
                $action = 0;
            } elseif ($action > MAX_ACTION) {
                $action = MAX_ACTION;
            }
            $this->_parsed->action = $action;

            $updates = $this->_to_state($this->_parsed);
            $this->_update(UPDATE_STATE, $updates);
            return $this;
        }
    }

    function tags() {
        return $this->_parsed->tags;
    }
    
    function date() {
        $date = $this->_parsed->date;
        $fdate = '';
        if ( ! empty($date)) {
            $fdate = utf8_encode(strftime(\tpp\config('date_format'), $date));
        }
        return $fdate;
    }

    /**
     * Returns the Project to which this task belongs.
     *
     * @return type
     */
    function project_name() {
        return $this->_parsed->project_name;
    }
    function project_key() {
        return $this->_parsed->project_key;
    }
    function project_index() {
        return $this->_parsed->project_index;
    }


    /**
     * Set/get the plain text value of the task.  Can actually be multiple tasks, info, or project items.
     *
     * Includes all notes, done, star, tags; the whole thing as in a plain text file.
     * Used for edit boxes.
     *
     * @param string $value new plain text of task
     * @return string
     */
    function raw($value = null) {
        static $parser = null;

        if ( ! is_null($value)) {
            // only create th eparser once, to save time
            if (is_null($parser)) {
                $parser = new storage\Parser();
            }
            // date/period tags will be expanded (usually what user wants!)
            $value = $parser->expand_interval_tags($value);
        }
        return parent::raw($value);
    }


    /**
     * If Taskpaper items can be deleted/archived.
     *
     * @return bool
     */
    function restricted() {
        return self::is_restricted();
    }


    /**
     * First few states correspond with actions, followed by no-state, then done tasks
     * this produces a more logical sorting order for states
     */
    private function _to_state(\StdClass $parsed) {
        if ($parsed->done) {
            $state = 0;
        } elseif ($parsed->action <= MAX_ACTION) {
            $state = $parsed->action + 1;
        } else {
            $state = 1;
        }
        return array($state, $parsed->key);
    }


    private function _rebuild_raw (\StdClass $parsed) {
        global $term;
        $done = $action = $tags = $date = $note = '';

        $pfx = $term['task_prefix'] . ' ';
        $text = $parsed->text;

        if ($parsed->done) {
            $done = $term['done_prefix'];
        }
        if ( ! empty($parsed->tags)) {
            $tags = ' ' . $this->_add_tag_symbol($parsed->tags);
        }
        if ( ! empty($parsed->date)) {
            $date = strftime(\tpp\config('date_format'), $parsed->date);
            $date = ' ' . $this->_add_tag_symbol($date);
        }
        if ($parsed->action > 0) {
            $action = ' ' . str_repeat($term['action_suffix'], $parsed->action);
        }

        $raw = $done . $pfx . $text . $tags . $date . $action;
        return $raw;
    }


    private function _add_tag_symbol($tags) {
        global $term;

        $pfx = $term['tag_prefix'];

        if ( ! is_array($tags)) {
            $tags = array($tags);
        }
        foreach ($tags as &$tag) {
            $tag = $pfx . $tag;
        }
        return implode(' ' ,$tags);
    }


    function _update($edit_type, $data = null) {
        $this->_parsed->raw = $this->_rebuild_raw($this->_parsed);
        self::update($edit_type, $data);
    }
}