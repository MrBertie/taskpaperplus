<?php
require_once(APP_PATH . 'inc/content.class.php');
require_once(APP_PATH . 'inc/search.class.php');

/**
 * Models the Taskpaper Application
 *
 * Required to initialise the various taskpapers: active, archive, available list
 * Creates default taskpapers for both if config is wrong or missing
 * Provides access to the currently active Taskpaper and the archive path
 *
 * @class Taskpapers    = the app, all taskpapers, and methods to use them
 * @class Taskpaper     = a Taskpaper, corresponds to a text file
 * @class TaskItems     = a group of tasks, all or filtered; accessible as an array or iterator
 * @class TaskItem      = a single TaskItem (stored as string in array)
 *
 *
 **/
class Taskpapers {
    private $_active;
    private $_active_file = '';
    private $_files;

    /**
     *
     * @param string $taskpaper_folder  = location of taskpaper files (from server root)
     * @param string $archive_file      = name of archive file (no extension)
     *
     * Active taskpaper is set via a property ->active($name)
     */
    function __construct($tab_name, Files $files) {

        $this->_files = $files;
        $this->_active_file = $tab_name;
        $this->active($this->_active_file);
    }
    /**
     * Set or get currently active taskpaper instance
     * If no active then uses first available tab
     *
     * Used by the dispatcher and templates to work with the
     * active taskpaper: edit, delete, search, etc...
     *
     * @param <string> $name    = name of a different taskpaper, changes to this one
     * @return <TaskPaper>      = returns the current (or new) taskpaper instance
     */
    function active($name_or_idx = null) {
        // if the active is already set then get it
        if ($name_or_idx === null && isset($this->_active)) {
            log&&msg(__METHOD__, 'getting the currently active taskpaper:', $this->_active_file);
            return $this->_active;
        }
        // if an existing name|idx was provided use it;
        // NOTE: the real file must exist, not just the cached version!
        $name = $this->_files->resolve_name($name_or_idx);
        // if name was invalid then return the first tab (excl. the archive)
        if (!$name) $name = $this->_files->first();
        $this->_active_file = $name;
        // return a new instance of the active taskpaper
        $this->_active = $this->_taskpaper($name);
        log&&msg(__METHOD__, 'returning a new taskpaper instance');
        return $this->_active;
    }
    function exists($name) {
        return $this->_files->exists($name);
    }
    /**
     * Removes the active taskpaper file to the _deleted folder
     * or specific taskpaper file if $name is provided
     * returns the next available taskpaper tab (name only)
     */
    function delete($name = null) {
        $name = ($name === null) ? $this->_active_file : $name;
        if ( ! $this->_restricted($name)) {
            $next_tab = $this->_files->delete($name);
            return $next_tab;
        } else {
            return false;
        }
    }
    /**
     * Renames the active taskpaper file
     * @return string the new name (may be santitised!)
     */
    function rename($name) {
        $name = $this->_files->rename($this->_active_file, $name);
        $this->_active_file = $name;
        $this->_active->content->file_name = $name;
        return $name;
    }
    /**
     * Creates a new taskpaper; however does not reset the active one
     * call active($name) to active the new taskpaper
     *
     * @param <string> $name name of new taskpaper file; could be sanitised!
     * @return new taskpaper name or false if it failed
     */
    function create($name) {
        $name = $this->_files->create($name);
        log&&msg(__METHOD__, "creating a new taskpaper called: $name");
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
            return $this->_taskpaper($name);
        } else {
            return false;
        }
    }
    function items() {
        return $this->_files->items();
    }
    function count() {
        return $this->_files->count();
    }
    // needed by TaskItems in order to archive items
    function archive_path() {
        return $this->_files->archive_path();
    }
    function trash_path() {
        return $this->_files->trash_path();
    }

    // trash|archive tasks cannot be archived; therefore some tools are not available
    private function _restricted($name) {
         return $name == FILE_ARCHIVE || $name == FILE_TRASH;
    }
    private function _taskpaper($name) {
        $restricted = $this->_restricted($name);
        $content = new Content($this->_files->fullpath($name), $name, $restricted);
        return new Taskpaper($this, $content);
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
class Taskpaper {
    public $content;    // TODO: currently allow direct access to content; is this necessary?
    public $tasks;      // for ease of use, allows direct array access of tasks
    private $_taskpapers;
    private $_name = '';

	/**
     * @param Taskpapers $taskpapers    instance of parent taskpaper application class
     * @param Content    $content       instance of content class for this taskpaper
     * @param Bool       $restricted    trash|archive have special display needs
     */
    function __construct(Taskpapers &$taskpapers, Content $content) {
        $this->_taskpapers = $taskpapers;
        $this->content = $content;
        $this->content->update();
        $this->_name = &$content->file_name;
        $this->tasks = new TaskItems($content, $taskpapers->archive_path(), $taskpapers->trash_path());
    }
    // read-only: swap taskpaper name via taskpapers only!
    function name() {
        return $this->_name;
    }
    /**
     * List of all tasks
     * @return TaskItems    array/iterator of TaskItem objects
     */
    function tasks() {
        return $this->tasks;
    }
    /**
     * Returns the content of the current taskpaper text file
     * @return String   text file content
     */
    function plain_text() {
        return $this->content->plain_text;
    }
    /**
     * Returns an array of all tags
     * @return  Array of strings
     */
    function tags() {
        return $this->content->all_tags;
    }
    /**
     * Returns a list of all projects
     * @return Array of strings
     */
    function projects() {
        return $this->content->all_projects;
    }
    /**
     * Returns a new Task Search object
     * which provides methods to search by: command, tag, project, filter, expression (incl. date or time period)
     * @return Search object
     */
    function search() {
        return new Search($this->content);
    }
    /**
     * Updates (or syncs) the text file and session data
     * based on file times, and updates the current in-memory data
     * where necessary
     * @see save_edits
     */
    function update() {
        $this->content->update();
    }
    /**
     * Saves the user's edits/changes to the taskpaper text file
     *
     * This could be:
     * EDIT_PLAINTEXT: direct edits, i.e. by passing in the changes plain text string
     * EDIT_CACHE:  where for example a line was deleted, edited, changed
     *              In this case 'update' uses the current in-memory TaskData to recreate the
     *              edited tasks)
     * EDIT_STATE: where only state was changed (state highlighting, completed)
     *             only the text file needs to be updated, cache is updated in-place, and
     *             doesn't need to be rebuilt (expensive process)
     *
     * @param String $edited_tasks  = a plain text string of tasks
     */
    function save_edits($edit_type, $edited_tasks = null) {
        $this->content->save_edits($edit_type, $edited_tasks);
    }
    /**
     * Is this the archive|trash taskpaper?
     * @return bool True if this is restricted (i.e. archive|trash)
     */
    function restricted() {
        return $this->content->restricted;
    }
}


class BasicItems implements ArrayAccess, Iterator {
    protected $_content;
    protected $_raw_tasks = array();

    function __construct(Content $content) {
        $this->_content = $content;
        $this->_raw_tasks = &$content->raw_tasks;
    }
    function item($key) {
        // returns the specified task item as (Basic)Item object
        switch ($this->_content->item_type[$key]) {
            case ITEM_TASK:
                return new TaskItem($this->_content, $key);
                break;
            case ITEM_PROJ:
                return new ProjectItem($this->_content, $key);
                break;
            case ITEM_LABEL:
                return new LabelItem($this->_raw_tasks[$key], $key);
                break;
            default:
                return new BasicItem($this->_content, $key);
        }
    }
    function task_count() {
        return $this->_content->task_count;
    }
    function project_count() {
        return $this->_content->project_count;
    }
    function tag_count() {
        return $this->_content->tag_count;
    }

    // *** Iterator interface ***
    function rewind() {
        reset($this->_raw_tasks);
    }
    function current() {
        if ($this->valid()) {
            return $this->item($this->key());
        } else {
            return false;
        }
    }
    function key() {
        return key($this->_raw_tasks);
    }
    function next() {
        next($this->_raw_tasks);
    }
    function valid() {
        return current($this->_raw_tasks) !== false;
    }

    // *** ArrayAccess interface ***
    public function offsetSet($offset, $value) {
        // NOTE: this makes no sense right now, edit TaskItem class directly instead
    }
    public function offsetExists($offset) {
        return $this->_exists($offset);
    }
    public function offsetUnset($offset) {
        unset($this->_raw_tasks[$offset]);
    }
    public function offsetGet($offset) {
        return $this->_exists($offset) ? $this->item($offset) : null;
    }

    private function _exists($key) {
        return isset($this->_raw_tasks[$key]);
    }
}
/**
 * Class FilteredTasks
 * Read-only list of tasks, Title() => filter used
 * Separated by tasks, and projects
 * Accessible by array/iterator
 */
class FilteredItems extends BasicItems {
    private $_projects = array();
    private $_project_count = 0;
    private $_task_count = 0;
    private $_title = '';
    /**
     * @param Content $content
     * @param array $tasks
     * @param integer $task_count
     * @param array $projects
     * @param integer $project_count
     * @param string $title usually the filter used to produce this
     */
    function __construct(Content $content, Array $tasks, $task_count = 0,
                         $projects = array(), $project_count = 0, $title = '') {
        $this->_content = $content;
        $this->_raw_tasks = $tasks;
        // return a new filtered task list for the projects only
        if ($project_count > 0) {
            $this->_projects = new FilteredItems($this->_content, $projects, $project_count);
            $this->_project_count = $project_count;
        } else {
            $this->_projects = array();
            $this->_project_count = 0;
        }
        $this->_task_count = $task_count;
        $this->_title = $title;
    }
    function projects() {
        return $this->_projects;
    }
    function project_count() {
        return $this->_project_count;
    }
    function task_count() {
        return $this->_task_count;
    }
    function title() {
        return $this->_title;
    }
}
/**
 * Class TaskItems
 * Represents all tasks in a given taskpaper
 * Allows array and iterator access, and provides basic
 * methods to add, edit, delete and archive tasks by key
 * @method add
 * @method delete
 * @method archive
 * @method archive_all
 * @method no_star
 * @method plain_text
 * @param content => instance of Content
 * @param archive_path => needed by archive method to find the archiving filepath
 */
class TaskItems extends BasicItems {
    private $_archive_path = '';
    private $_trash_path = '';

    function __construct(Content $content, $archive_path, $trash_path) {
        $this->_content = $content;
        $this->_raw_tasks = & $content->raw_tasks;
        $this->_archive_path = $archive_path;
        $this->_trash_path = $trash_path;
    }
	/**
	* adds a new task into the curent taskpaper
	* @param int $into_project the project key (not number!) into which to insert
	*/
    function add($new_task, $into_project = 0) {
        $pfx = config('note_prefix');
        $new_task = str_replace($pfx, "\n" . $pfx, $new_task);  // split notes into separate lines
        $new_task = $this->_content->expand_interval_tags($new_task);
        $max = $this->_content->project_count - 1;
        $into_project = ($into_project > $max) ? $max : $into_project;
        // insert at end of list if last project or no projects exist (edge case)
        if ($into_project == $max || $max == 0) {
            $edited_tasks = $this->plain_text() . "\n" . $new_task;
            $this->_content->save_edits(EDIT_PLAINTEXT, $edited_tasks);
            return true;
        // or insert into middle (at end of project's task list)
        } elseif ($into_project < $max) {
            $pos = array_search($into_project + 1, $this->_content->project_index);
            $insert = array('0' . count($this->_raw_tasks) => $new_task);
            $this->_raw_tasks = array_insert($this->_raw_tasks, $pos, $insert);
            $this->_content->save_edits(EDIT_CACHE);
            return true;
        }
        return false;
    }
    function delete($key) {
        // deletes from deleted are gone forever!
        if ($this->_content->file_name != $this->_trash_path) {
            $this->_archive_to($key, $this->_trash_path);
        }
        $this->_delete($key);
    }
    function archive($key) {
        $this->_archive_to($key, $this->_archive_path);
        $this->_delete($key);
    }
    function archive_all() {
        $done = config('done_prefix');
        foreach ($this->_raw_tasks as $key => $task) {
            if ($task[0] == $done) {
                $keys[] = $key;
            }
        }
        $this->_archive_to($keys, $this->_archive_path);
        $this->_delete($keys);
    }
    function no_actions() {
        $plain_text = preg_replace('/ [' . config('action_suffix') . ']{1,5}$/m', "", $this->_content->plain_text);
        $this->_content->save_edits(EDIT_PLAINTEXT, $plain_text);
    }
    function plain_text() {
        // return all tasks as a text string, ready for editing
        return $this->_content->plain_text;
    }

    /************* local ****************/

    private function _delete($keys) {
        if ( ! is_array($keys)) $keys = array($keys);
        foreach ($keys as $key) {
            unset($this->_raw_tasks[$key]);
        }
        $this->_content->save_edits(EDIT_STATE);
    }
    private function _archive_to($keys, $target) {
        // move this task to the archiving|trash taskpaper
        if ( ! is_array($keys)) $keys = array($keys);
        $tasks = '';
        foreach ($keys as $key) {
            $task = $this->_raw_tasks[$key];
            $tasks .= "\n" . $task . "\n" .
                      config('note_prefix') . "| " . $this->_content->file_name .
                      " | " . $this->_content->proj_name_by_task($key) .
                      " | " . date("d-M-Y H'i") . " |";
        }
        $file = fopen($target, 'a');
        fwrite($file, $tasks);
        fclose($file);
    }
}

class BasicItem {
    protected $_content;
    protected $_raw_task; // task item (reference)
    protected $_text = '';
    protected $_key;

    function __construct(Content &$content, $key) {
        $this->_key = $key;
        $this->_content = & $content;
        $this->_raw_task = & $this->_content->raw_tasks[$key];
        $this->_text = $this->_raw_task;
    }
    function key() {
        return $this->_key;
    }
    function text() {
        return $this->_text;
    }
}
/**
 * A neutral label in the task list
 */
class LabelItem extends BasicItem {
    function __construct($text, $key) {
        $this->_text = $text;
        $this->_key = $key;
    }
}
/**
 * A project line in the task list
 */
class ProjectItem extends BasicItem {
    public $_ord_text = '';

    function __construct($content, $key) {
        parent::__construct($content, $key);
        $this->_ord_text = $content->proj_name_by_key($key, true);
        $this->_text = $content->proj_name_by_key($key, false);
        $this->_key = $key;
    }
    function ord_text() {
        return $this->_ord_text;
    }
}
/**
 * Represents a task line, parsed into 'done, text, tags, star and notes'
 * All changes are made directly to the cache arrays; which is then updated accordingly
 * Can be set by element or as a whole via ->plain()
 * Can update itself
 */
class TaskItem extends BasicItem {
    // refers to a specific task
    protected $_parsed_task;

    function __construct(Content &$content, $key) {
        $this->_key = $key;
        $this->_content = & $content;
        $this->_parsed_task = & $this->_content->parsed_tasks[$key];
        $this->_raw_task = & $this->_content->raw_tasks[$key];
    }
    function text($value = '') {
        // get
        if (empty($value)) {
            return $this->_parsed_task->text;
        // set
        } else {
            $this->_parsed_task->text = $value;
            $this->_save_edits(EDIT_CACHE);
        }
    }
    /**
     * set or unset task as "Done" usually X at begining see config.php
     * current state highlighting will be removed!
     * @param boolean $value
     * @return boolean
     */
    function done($value = null) {
        if ($value === null) {
            return $this->_parsed_task->done;
        } else {
            $this->_parsed_task->done = (bool) $value;
            $this->_set_state();
            $this->_save_edits(EDIT_STATE);
        }
    }
    function action($value = null) {
        // set or unset the state highlighting
        if ($value === null) {
            return $this->_parsed_task->action;
        } else {
            $value = ($value <= MAX_ACTION) ? (int) $value : 0;
            $this->_parsed_task->action = $value;
            $this->_set_state();
            $this->_save_edits(EDIT_STATE);
        }
    }
    /**
     * first few states correspond with actions, followed by no-state, then done tasks
     * this produces a more logical sorting order for states
     * NOTE: this has nothing to do with app->user->state!
     */

    private function _set_state() {
        if ($this->_parsed_task->done === true) {
            $state = MAX_ACTION + 1;
        } elseif ($this->_parsed_task->action <= MAX_ACTION) {
            $state = $this->_parsed_task->action;
        } else {
            $state = 0;
        }
        // update cache directly
        $this->_content->task_state[$this->_key] = $state;
    }
    function notes() {
        return $this->_parsed_task->notes;
    }
    function has_notes() {
        return (!empty($this->_parsed_task->notes)) ? true : false;
    }
    function tags() {
        return $this->_parsed_task->tags;
    }
    function has_tags() {
       return (!empty($this->_parsed_task->tags)) ? true : false;
    }
    function project_name() {
        return $this->_content->proj_name_by_task($this->_key);
    }
    function project_key() {
        $num = $this->_content->task_project[$this->_key];
        $key = array_search($num, $this->_content->project_index);
        return $key;
    }
    /**
     * Set/get the plain text value of the task,
     * incl. notes, done, star, tags; the whole thing as in file
     *
     * @param string $value new plain text of task
     * @return string
     */
    function plain($value = null) {
        if ($value === null) {
            return $this->_raw_task;
        } else {
            // this is already in plain text format so no rebuilding necessary
            // tags will also be expanded (usually what user wants!)
            $value = $this->_content->expand_interval_tags($value);
            $this->_raw_task = $value;
            $this->_content->save_edits(EDIT_CACHE);
        }
    }
    function restricted() {
        return $this->_content->restricted;
    }
    private function _rebuild_plain() {
        $done = ($this->_parsed_task->done === true) ? config('done_prefix') : '';
        $action = str_repeat(config('action_suffix'), $this->_parsed_task->action);
        if (!empty($action)) $action = ' ' . $action;   // ensure a space before action symbols
        $pfx = " " . config('tag_prefix');
        $tags = ($this->has_tags() === true) ? $pfx . join($pfx , $this->_parsed_task->tags) : '';
        $pfx = config('note_prefix');
        $notes = '';
        foreach ($this->_parsed_task->notes as $note) {
            if ($note->type == SINGLE_NOTE) {
                $notes .= "\n" . $pfx . $note->text;
            } elseif ($note->type == BLOCK_NOTE) {
                $notes .= "\n" . $pfx . "\n" . $note->text . $pfx;
            }
        }
        $plain = $done . config('task_prefix') . rtrim($this->_parsed_task->text) . $tags . $action . $notes;
        return $plain;
    }
    private function _save_edits($edit_type) {
        $this->_raw_task = $this->_rebuild_plain();
        $this->_content->save_edits($edit_type);
    }
}

class Note {
    function __construct($text, $type = SINGLE_NOTE) {
        $this->text = $text;
        $this->type = $type;
    }
}

?>