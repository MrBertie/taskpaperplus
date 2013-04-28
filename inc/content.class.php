<?php
namespace tpp\model;


/**
 * Simple data structure: all data for one Taskpaper; easily serialised.
 */
class Content {

    // Taken from file name and path
    /**
     * @var string  Full file path to this taskpaper (incl. name and extension).
     */
    public $file_path = '';
    /**
     * @var string  File name of this taskpaper file (name only).
     */
    public $name = '';
    /**
     * @var enum    Type of Tab that this taskpaper represents.
     */
    public $tab_type = TAB_NORMAL;


    // Taken from ast only
    /**
     * @var string  The title of this taskpaper.
     */
    public $title = '';
    /**
     * @var string  Title and its Note as raw text
     */
    public $raw_title = '';
    /**
     * @var string  Note for this Taskpaper
     */
    public $note = '';
    /**
     * @var string  The tab position of this taskpaper.
     */
    public $index = -1;

    /**
     * @var string   Plain text string of full task list (for edit box).
     */
    public $raw = '';

    /**
     * @var array   item_key => full plain-text item.
     *
     * For all elements: Project, Task, Info; incl. respective notes.
     * Used only for full-text searching
     */
    public $raw_items = array();
    /**
     * @var array   item_key => parsed version of ALL item types (as arrays).
     */
    public $parsed_items = array();
    /**
     * @var array tag_number => tag_name
     */
    public $tags = array();


    /* indices | sorting columns */

    /**
     * @var array   item_key => type of each item_key (Project, Task, Label, etc...)
     */
    public $types = array();
    /**
     * @var array   project_index => item_key (only Projects)
     */
    public $projects = array();
    /**
     * @var array   item_key  => project_index (reverse lookup)
     *
     * I.e. which items are Projects?
     */
    public $project_index = array();


    /**
     * @var array   number => item_key (only tasks).
     *
     * I.e. which items are Tasks?
     */
    public $tasks = array();
    /**
     * @var array   item_key => item_key (only project items).
     *
     * Which project does this Task|Info belong to?
     */
    public $task_project = array();
    /**
     * @var array   item_key  => todo date (only Tasks)
     */
    public $task_date = array();
    /**
     * @var array   item_key  => Task state (only Tasks).
     *
     * (None, Next, Pending, Maybe, Done)
     */
    public $task_state = array();


    /**
     * @var integer cached Task count
     */
    public $task_count = 0;
    /**
     * @var integer cached Project count
     */
    public $project_count = 0;
    /**
     * @var integer cached Tag count
     */
    public $tag_count = 0;
    /**
     *
     * @var count of all items
     */
    public $count = 0;



    /**
     * Returns the project based on its item_key (not index number!).
     *
     * @param integer $key parsed_item item_key
     * @return object parsed Project
     */
    function project_by_key($key) {
        $project = $this->parsed_items[$key];
        return $project;
    }


    /**
     * Returns the project based on its ordinal position in the list (= number in sidebar).
     *
     * @param integer $index The project key
     * @return object ParsedProject
     */
    function project_by_index($index) {
        $project = $this->parsed_items[$this->projects[$index]];
        return $project;
    }


    /**
     * Return a project based on the key of a task that belongs to it.
     *
     * @param string $key A task index key
     * @return object ParsedProject class
     */
    function project_by_task($key) {
        $project_key = $this->projects[$this->task_project[$key]];
        $project = $this->parsed_items[$project_key];
        return $project;
    }


    /**
     * Re-order the *raw_task* list based on a jQuery Sortable list of IDs.
     *
     * Used when user does a drag n drop sort on the task list.
     * Sorting is done in-place, nothing is returned.
     *
     * see: http://stackoverflow.com/questions/348410/sort-an-array-based-on-another-array
     * for details of how this works
     *
     * @param array $order  New sorting order (array of IDs)
     * @param string $project Specific Project that should be sorted
     */
    function reorder($order, $project = null) {
        $order = array_flip($order);
        //$sorted_items = array_intersect_key($this->parsed_items, $order);
        $sorted_items = array_merge($order, $this->parsed_items);
        //$sorted_items = array_merge($this->parsed_items, $sorted_items);

        // deal with sorting one project by itself
        if ($project !== null) {
            $pos = array_search($project, $this->project_index);
            $this->parsed_items = array_insert($this->parsed_items, $pos, $sorted_items, 1, true);
        } else {
            $this->parsed_items = $sorted_items;
        }
    }


// Currently unused, maybe added sometime to allow
// entire projects to be removed

//    /**
//     * Remove a Project and all its child Tasks and Info lines
//     */
//    private function remove_project($key) {
//        if (array_key_exists($key, $this->raw_items)) {
//            // remove all tasks in this project first
//            $id = $this->project_index[$key];
//            $to_remove = array_keys($this->task_project, $id);
//            foreach (keys($to_remove) as $key) {
//                $this->remove_task($key);
//            }
//            // now remove the project itself
//            unset($this->raw_items[$key],
//                  $this->projects[$id],
//                  $this->project_index[$key]
//                  );
//            $this->project_count--;
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//
//    /**
//     * Remove either tasks or info lines.
//     */
//    private function remove_task($key) {
//        if (array_key_exists($key, $this->raw_items)) {
//            unset($this->raw_items[$key],
//                  $this->tasks[$key]
//                  );
//            if ($this->types[$key] == ITEM_TASK) {
//                unset($this->task_date[$key],
//                      $this->task_state[$key],
//                      $this->task_project[$key]
//                      );
//            }
//            unset ($this->types[$key]);
//            $this->task_count--;
//            return true;
//        } else {
//            return false;
//        }
//    }
}






class ContentBuilder {

    const OFFSET = 2;

    private $_cont;
    private $_offset;

    const START = '00';


    function build(\StdClass $ast, $name = null, $filepath = null) {
        $this->_cont = new Content;
        $this->reset_key();

        $this->add_page($ast);
        $this->set_up($name, $filepath);
        $this->refresh();
        $this->_cont->raw = $this->raw_items_to_raw($this->_cont);
        return $this->_cont;
    }


    function rebuild(\StdClass $ast, Content $content) {
        return $this->build($ast, $content->name, $content->file_path);
    }


    /**
     * Rebuild raw_items & raw from parsed_items
     * Mainly done after small state changes and sorting
     */
    function rebuild_from_parsed(Content &$content) {
        $content->raw_items = $this->_rebuild_raw_items($content->parsed_items);
        $content->raw = $this->_rebuild_raw($content->raw_items,
                                            $content->raw_title);
        return $content;
    }


    /**
     * Used by the to
     * @param \tpp\model\Content $content
     * @return type
     */
    function parsed_items_to_raw(Content $content) {
        $raw_items = $this->_rebuild_raw_items($content->parsed_items);
        $raw_title = $content->raw_title;
        return $this->_rebuild_raw($raw_items, $raw_title);
    }


    function raw_items_to_raw(Content $content) {
        $raw_items = $content->raw_items;
        $raw_title = $content->raw_title;
        return $this->_rebuild_raw($raw_items, $raw_title);
    }


    private function _rebuild_raw_items(Array $parsed_items) {
        $raw_items = array_map(array($this, '_get_raw'), $parsed_items);
        return $raw_items;
    }


    /**
     * Rebuilds the raw text file.
     *
     * Make raw presentation look better, adds extra blank lines before projects, and insesrts the title
     * @param array $raw_items
     * @param string $raw_title
     * @return string
     */
    private function _rebuild_raw(Array $raw_items, $raw_title = '') {
        $raw = ( ! empty($raw_title)) ? $raw_title . "\n\n" : '';
        $raw .= implode("\n", $raw_items);
        return $raw;
    }


    private function next_key() {
        $key = '0' . $this->_offset;
        $this->_offset += 10;
        return $key;
    }


    private function reset_key() {
        $this->_offset = self::START;
    }


    private function add_page(\StdClass $parsed) {
        $this->_cont->title = $parsed->text;
        $this->_cont->note = $parsed->note;
        $this->_cont->index = ( ! empty($parsed->index) ? (int) $parsed->index + self::OFFSET : '');
        $this->_cont->raw_title = $this->_get_raw($parsed);
        $this->_cont->project_count = count($parsed->children);

        foreach ($parsed->children as $project) {
            $this->add_project($project);
        }
    }


    private function add_project(\StdClass $parsed) {

        $key = $this->_basic($parsed, $parsed->index > 0);

        $this->_cont->projects[$parsed->index] = $key;
        $this->_cont->project_index[$key] = $parsed->index;    // reverse lookup

        foreach($parsed->children as $item) {
            $add = 'add_' . $item->type;
            $this->$add($item, $parsed);
        }

        // add a raw blank line below project for better raw display
        if ( ! ($parsed->index == 0 && empty($parsed->children)))  {
            $this->_cont->raw_items[] = '';
        }
    }


    private function add_task(\StdClass $parsed, $proj) {
        $key = $this->_basic($parsed);
        $this->_basic_item($parsed, $proj, $key);

        // Create searching|sorting arrays unique to tasks
        $this->_cont->tasks[] = $key;  // only tasks
        $this->_cont->task_date[$key] = $parsed->date;
        $this->_cont->task_state[$key] = ($parsed->done) ? 0 : $parsed->action + 1;
        $this->_cont->tags = array_merge($this->_cont->tags, $parsed->tags);
        $this->_cont->task_count++;
    }


    /**
     * DB items set by task, info types only
     * @param \StdClass $parsed
     * @param type $key
     */
    private function add_info(\StdClass $parsed, $proj) {
        $key = $this->_basic($parsed);
        $this->_basic_item($parsed, $proj, $key);
    }


    private function _basic_item(\StdClass $parsed, $proj, $key) {
        $parsed->project_key = $proj->key;
        $parsed->project_name = $proj->text;
        $parsed->project_index = $proj->index;
        $this->_cont->task_project[$key] = $proj->index;
    }


    /**
     * DB items set by all item types.
     *
     * @param \StdClass $parsed
     * @param type $key
     * @param type $no_raw
     */
    private function _basic(\StdClass $parsed, $get_raw = true) {
        $key = $this->next_key();
        $parsed->key = $key;
        $this->_cont->types[$key] = $parsed->type;
        $this->_cont->parsed_items[$key] = $parsed;
        if ($get_raw) {
            $this->_cont->raw_items[$key] = $this->_get_raw($parsed);
        }
        return $key;
    }


    private function _get_raw(\StdClass $parsed) {
        $raw = $parsed->raw . ($parsed->note->len > 0 ? "\n" . $parsed->note->raw : '');
        return $raw;
    }


    /**
     * Sets up the Content, including the correct tab names for Trash and Archive, tag index, task and item count.
     */
    private function set_up($name, $file_path) {
        $this->_cont->name = $name;
        $this->_cont->file_path = $file_path;

        // Confirm the Tab type
        if ($name == FILE_TRASH) {
            $this->_cont->tab_type = TAB_TRASH;
            $this->_cont->index = 1;
            $this->_cont->title = \tpp\lang('trash_lbl');   
        } elseif ($name == FILE_ARCHIVE) {
            $this->_cont->tab_type = TAB_ARCHIVE;
            $this->_cont->index = 2;
            $this->_cont->title = \tpp\lang('archive_lbl');
        }
    }


    private function refresh() {
        // Get a sorted list of all unique tags with frequency of use.
        natcasesort($this->_cont->tags);
        $this->_cont->tags = array_count_values($this->_cont->tags);
        $this->_cont->tag_count = count($this->_cont->tags);

        $this->_cont->task_count = count($this->_cont->tasks);
        $this->_cont->count = count($this->_cont->raw_items);
    }
}