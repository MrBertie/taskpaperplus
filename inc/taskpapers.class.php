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
 * Provides access to the currently active Taskpaper and the archive path
 */
class Taskpapers {

    private $_active;          // active Taskpaper instance
    private $_active_file; // active File instance
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
        $this->_tabs = $cache->fetch_tabs();
        $this->active($active_tab);
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
     * Set or get currently active taskpaper instance.
     *
     * If no active then uses first available [user] tab.
     *
     * @param string $name  name of a different taskpaper, changes to this one.  Can also be an array index 0...n or a reverse index -1...-n (from end)
     * @return object   returns the current (or new) taskpaper instance
     */
    function active($name = null) {

        // if the active is already set then get it
        if (is_null($name) && isset($this->_active)) {

            \log&&msg(__METHOD__, 'getting the currently active taskpaper:', $this->_active_file);

            return $this->_active;

        } else {

            // if an existing name|idx was provided use it;
            // NOTE: the real file must exist, not just the cached version!
            $file = $this->_files->item($name);

            // if name was invalid then return the first [user] tab
            if ( ! $file) $file = $this->_files->first();
            $this->_active_file = $file;

            // return a new instance of the active taskpaper
            $this->_active = $this->_get_taskpaper($file->name);

            \log&&msg(__METHOD__, 'returning a new taskpaper instance');

            return $this->_active;
        }
    }

    function exists($name) {
        return $this->_files->exists($name);
    }

    /**
     * Removes the active taskpaper file to the _deleted folder or specific taskpaper file if $name is provided.
     *
     * @return string The next available taskpaper tab (name only)
     */
    function delete($name = null) {
        $name = ($name === null) ? $this->_active_file->name : $name;
        if ( ! $this->_active_file->restricted()) {
            $next_tab = $this->_files->delete($name);
            $this->_active = null;
            $this->_active_file = '';
            $this->_cache->refresh();
            return $next_tab->name;
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
            $this->_active->name($file->name);
            return $file->name;
        } else {
            return false;
        }
    }

    /**
     * Creates a new taskpaper.
     *
     * NOTE: does not reset the active one; call active($name) to active the new taskpaper
     *
     * @param string    $name name of new taskpaper file; could be sanitised!
     * @return string   new taskpaper name or false if it failed
     */
    function create($name, $text) {
        $name = $this->_files->create($name, $text);
        $this->_cache->refresh();
        // TODO: selected tab is not being reset

        \log&&msg(__METHOD__, "created a new taskpaper called: $name");

        return $name;
    }

    /**
     * returns a different taskpaper instance, without being the active,
     * for future API use
     */
    function item($name) {
        if ($name == $this->_active_file) {
            return $this->active();
        } elseif ($this->exists($name)) {
            return $this->_get_taskpaper($name);
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


class TaskpaperPersist {

    protected static $_content;
    protected static $_cache;
    protected static $_name;

    function __construct(storage\Cache $cache, $name) {
        self::$_cache = $cache;
        self::$_content = $cache->fetch($name);
        self::$_name = $name;
    }

    /**
     * Refreshes (or syncs) the text file and cache (session) data based on last modified times
     *
     * @see Cache->refresh()
     */
    function refresh() {
        self::$_content = self::$_cache->refresh()->fetch(self::$_name);
    }

    /**
     * Saves the user's edits/changes to the taskpaper text file.
     *
     * This could be:
     * UPDATE_RAW: direct edits, i.e. by passing in the changes plain text string
     * UPDATE_PARSED:  where for example a line was deleted, edited, changed
     *              In this case 'update' uses the current in-memory TaskData to recreate the
     *              edited tasks)
     * UPDATE_STATE: where only state was changed (state highlighting, completed)
     *             only the text file needs to be updated, cache is updated in-place, and
     *             doesn't need to be rebuilt (avoids an expensive process)
     *
     * @param String $raw   plain text string of tasks
     */
    function update($edit_type, $updates = null) {
        self::$_content = self::$_cache->update(self::$_content, $edit_type, $updates);
    }

    function restricted() {
        return (self::$_content->tab_type != TAB_NORMAL);
    }

    protected function _raw($key, $text = null) {
        if (is_null($text)) {
            return self::$_content->raw_items[$key];
        } else {
            self::$_content->raw_items[$key] = $text;
            $this->update(UPDATE_RAWITEM);
            return $this;
        }
    }
}



/**
 * Models a single taskpaper, usually the currently active one
 * Constructs arrays from the taskpaper text file (or session if not changed).
 * Includes tasks, tags, projects, and various indexes needed for filtering and searching
 * All taskpaper data is build by and stored in TaskData
 *
 * Provides methods to add, remove, edit and search, and access the tag and
 * project lists. Plus to save and update the tasks
 *
 * NOTE: taskpaper has no notion of state; that is left to the Dispatcher and State objects
 * It is concerned with content and manipulating it
 */
class Taskpaper extends TaskpaperPersist {

    private $_trash_path = '';
    private $_archive_path = '';
    public $items;

    /**
     * @param storage\cache $cache
     */
    function __construct(storage\Cache $cache, $name, $trash_path, $archive_path) {
        parent::__construct($cache, $name);

        $this->_archive_path = $archive_path;
        $this->_trash_path = $trash_path;
        $this->items = new Items($this, self::$_content);
    }

    /**
     * Set or Get the Taskpaper name.
     */
    function name($name = null) {
        if (is_null($name)) {
            return self::$_name;
        } else {
            self::$_content->name = self::$_name = $name;
            return $this;
        }
    }

    /**
     * List of ALL item types: Page, Project, Task, Info.
     *
     * @return AllItems    array/iterator of ALL Item types
     */
    function items() {
        $items = new Items($this, self::$_content);
        return $items;
    }

    /**
     * Adds a new task into the curent taskpaper.
     *
     * This is used when adding new tasks from the input box.
     *
     * @global array $token
     * @param string $new_task   The new task as text, as typed by the user, can include notes, and a project identifier ('/2')
     * @param int    $project_id The project id (not number!) into which to insert
     * @return boolean  true on success
     */
    function add($new_task, $project_index = 0) {
        $max = self::$_content->project_count - 1;
        $project_index = ($project_index > $max) ? $max : $project_index;

        // insert at end of list if last project or only 1 project exist (edge case)
        if ($project_index == $max || $max == 0) {
            $raw = $this->raw() . "\n" . $new_task;
            self::update(UPDATE_RAW, $raw);
            return true;

        // or insert into a specific Project (at end of project's task list)
        } elseif ($project_index < $max) {
            $key = array_search($project_index + 1, self::$_content->project_index);
            $this->replace($key, $new_task);
            return true;
        }
        return false;
    }

    function replace($key, $text) {
        $new_item = array('new' => $text);
        self::$_content->raw_items = \tpp\array_insert(self::$_content->raw_items, $key, $new_item);
        self::update(UPDATE_PARSED);
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
        return new ProjectItems($this, self::$_content);
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

    function reorder($new_order, $project = null) {
        self::$_content->reorder($new_order, $project = null);
        self::update(UPDATE_STATE);
    }

    /**
     * Returns a new Task Search object which provides methods to search by: command, tag, project, filter, expression (incl. date or time period).
     *
     * @return object Search
     */
    function search() {
        return new Search($this, self::$_content);
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
//        if ( ! is_array($keys)) {
//            $keys = array($keys);
//        }
        $tasks = '';
        foreach ($keys as $key) {
            $task = self::$_content->parsed_items[$key];
            $tasks .= "\n" . $task->raw . "\n" . $term['note_prefix'] . "\n";
            if ($task->note->len > 0) {
                $tasks .= $task->note->text . "\n";
            }
            $tab_name = self::$_content->name;
            $project = self::$_content->project_by_task($key);
            $tasks .= \tpp\lang('deleted_lbl') . " "
                    . $tab_name
                    . " | " . $project->text
                    . " | " . date("d-M-Y H:i");
            $tasks .= "\n" . $term['note_prefix'];
        }
        $file = fopen($target_path, 'a');
        fwrite($file, $tasks);
        fclose($file);
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
        $this->_items = self::$_content->parsed_items;
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
        $this->_items = self::$_content->projects;
        $this->_count = self::$_content->project_count;
    }

    function item($index) {
        $index = self::$_content->project_by_index($index);
        return new ProjectItem($index);
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
                         $title = ''
                         ) {
        // filtered raw_items array: only the $keys are actually used
        $this->_items = $items;
        $this->_projects = new FilteredProjects($projects, $project_count);
        $this->_count = $count;
        $this->_title = $title;
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
}


class FilteredProjects extends ProjectItems {

    function __construct(Array $projects = array(), $project_count = 0) {
        $this->_items = $projects;
        $this->_count = $project_count;
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
        return parent::_raw($key, $value);
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
     * Usually signified by the presence|absence of the X at beginning of the task.
     * (see config.php to change the syntax)
     *
     * Current state highlighting will be restored should the task be "undone".
     *
     * @param boolean $value The new "Done" state (true/false)
     * @return boolean The current "Done" state
     */
    function done($value = null) {
        if (is_null($value)) {
            return $this->_parsed->done;
        } else {
            $this->_parsed->done = (bool) $value;
            $updates = $this->_new_state($this->_parsed);
            $this->_update(UPDATE_STATE, $updates);
        }
    }

    /**
     * Set/get the state "Action" , i.e. highlighting: next, wait, maybe, etc...
     *
     * @param integer $value Constant ACTION_* (states)
     * @return boolean
     */
    function action($value = null) {
        if (is_null($value)) {
            return $this->_parsed->action;
        } else {
            $value = ($value <= MAX_ACTION) ? (int) $value : 0;
            $this->_parsed->action = $value;
            $updates = $this->_new_state($this->_parsed);
            $this->_update(UPDATE_STATE, $updates);
        }
    }

    function tags() {
        return $this->_parsed->tags;
    }
    function date() {
        $date = $this->_parsed->date;
        if ( ! empty($date)) {
            $date = strftime(\tpp\config('date_format'), $date);
        }
        return $date;
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
            if (is_null($parser)) $parser = new storage\Parser();
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
        return self::restricted();
    }


    /**
     * First few states correspond with actions, followed by no-state, then done tasks
     * this produces a more logical sorting order for states
     */
    private function _new_state(\StdClass $parsed) {
        if ($parsed->done) {
            $state = MAX_ACTION + 1;
        } elseif ($parsed->action <= MAX_ACTION) {
            $state = $parsed->action;
        } else {
            $state = 0;
        }
        return array($state, $parsed->key);
    }

    private function _rebuild_raw(\StdClass $parsed) {
        global $term;
        // TODO: could this be improved too
        $done = ($parsed->done) ? $term['done_prefix'] : '';
        $done .= $term['task_prefix'];
        $action = str_repeat($term['action_suffix'], $parsed->action);
        $raw = $parsed->raw;
        $old_prefix = '/^' . $term['done_prefix'] . '?' . $term['task_prefix'] . '/';
        $raw = preg_replace($old_prefix, $done, $raw);
        $raw = preg_replace($term['action'], $action, $raw);
        return $raw;
    }

    function _update($edit_type, $data = null) {
        $this->_parsed->raw = $this->_rebuild_raw($this->_parsed);
        self::update($edit_type, $data);
    }
}