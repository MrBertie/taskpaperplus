<?php

/**
 * Manages the raw parsed taskpaper content and the cache
 * Also handles parsing the text file to a cached representation (currently plain arrays)
 *
 */
class Content {
    public $file_path = '';             // full path (inc. name and ext)
    public $file_name = '';             // file name of this taskpaper file (name only)
    public $restricted = false;         // is this the archive|trash file? (needed to adapt view template)

    // raw plain -text task list
    public $plain_text = '';            // (string) plain text string of full task list (for edit box)
    public $raw_tasks = array();        // task_key      => full plain-text task (all elements); tasks incl. notes
    public $item_type = array();        // task_key      => item type for each line (task, project, label, etc...)

    // parsed task elements
    public $parsed_tasks = array();     // task_key      => the task parsed into its individual elements (array of ParsedTask objects)

    // indices | sorting columns
    public $project_index = array();    // task_key      => project_key (only projects)
    public $task_project = array();     // task_key      => project_key (only tasks)
    public $task_date = array();
    public $task_state = array();

    public $all_tags = array();         // tag_number    => tag_name
    public $all_projects = array();     // project_key   => project_name

    // cached counts
    public $task_count = 0;
    public $project_count = 0;
    public $tag_count = 0;

    private $_cache = array();
    private $_cache_path = '';

    function __construct($file_path, $file_name, $restricted = false) {
        $this->file_path = $file_path;
        $this->file_name = $file_name;
        $this->restricted = $restricted;
        $this->_cache_path = config('cache_path') . $file_name;
    }
    /**
     * Update from cache to/from taskpaper file (newest only)
     * both are from disk, however the cache is pre-built, hence much quicker
     */
    function update() {
        $file_time = filemtime($this->file_path);
        if (file_exists($this->_cache_path)) {
            $cache_time = filemtime($this->_cache_path);
        } else {
            $cache_time = 0;
        }
        if ($cache_time >= $file_time) {
            $this->_from_cache();
        } else {
            $this->plain_text = file_get_contents($this->file_path);
            $this->_build_task_lists($this->plain_text);
            $this->_to_cache();
        }
    }
    /**
     * Save any taskpaper edits, whether internal or external (i.e. user)
     * Updates cache|file depending on $edit_type param
     *
     * @param const  EDIT_CACHE|EDIT_STATE|EDIT_PLAINTEXT => type of update necessary
     * @param string $edited_tasks plain text list of user edited tasks
     */
    function save_edits($edit_type, $edited_tasks = null) {
        if ($edit_type == EDIT_STATE) {
            // no need to rebuild entire cache for simple state changes (done|highlighting) or sorting
            $this->plain_text = $this->_build_plain_text($this->raw_tasks);

        } elseif ($edit_type == EDIT_CACHE) {
            // assume significant cache edit, i.e. tags, dates, added, position changes, etc...,
            // => full-rebuild, as other helper arrays need to be updated too!
            $plain_text = implode("\n", $this->raw_tasks);
            $this->_build_task_lists($plain_text);

        } elseif ($edit_type == EDIT_PLAINTEXT) {
            // mainly user edited plain-text (plus a few other internal edits of text directly)
            $this->_build_task_lists($edited_tasks);
        }
        file_put_contents($this->file_path, $this->plain_text);
        $this->_to_cache();
    }
    /**
     * Convert any interval tags (=today, =tomorrow, etc...) to real dates
     *
     * @param string $plain_task => a user typed task
     * @return string
     */
    function expand_interval_tags($plain_task) {
        // find any tags
        preg_match_all(config('interval_tok_rgx'), $plain_task, $matches, PREG_SET_ORDER);
        // do they match a time period?
        $interval_filter = new IntervalFilter($this);
        foreach ($matches as $match) {
            $orig_tag = $match[0];
            $date = $interval_filter->interval_as_date($orig_tag);
            if ($date !== false) {
                $plain_task = preg_replace('/' . $orig_tag . '/', "@" . date(config('date_format'), $date[1]), $plain_task);
            }
        }
        return $plain_task;
    }
    /**
     * Returns the project name based on task item key (not project number!)
     *
     * @param integer $key TaskItem index key
     * @return string The project name
     */
    function proj_name_by_key($key, $with_number = false) {
        $project = $this->all_projects[$this->project_index[$key]];
        if ($with_number === true) {
            $project = $this->project_index[$key] . '. ' . $project;
        }
        return $project;
    }
    /**
     *  Returns the project name, based on its place in the list (number in sidebar)
     *
     * @param integer $key  The project key/number
     * @param bool $with_number
     * @param bool $with_suffix
     * @return string   Project name with/without number and : suffix
     */
    function proj_name_by_num($number, $with_number = false) {
        $project = $this->all_projects[$number];
        if ($with_number === true) {
            $project = $number . '. ' . $project;
        }
        return $project;
    }
    function proj_name_by_task($key) {
        $project = $this->all_projects[$this->task_project[$key]];
        return $project;
    }
    /**
     * return project number based on its index key
     */
    function proj_num($key) {
        $number = $this->project_index[$key];
        return $number;
    }
    /**
     * re-order the raw_task list based on a jquery 'sortable' list of IDs
     */
    function reorder($new_order, $project = null) {
        // new task array: keys only
        $new_raw_tasks = array_flip(explode(',', $new_order));
        $raw_tasks = & $this->raw_tasks;
        // match tasks to new key locations
        foreach ($new_raw_tasks as $key => &$value) {
            $value = $raw_tasks[$key];
        }
        // deal with sorting one project by itself
        if ($project !== null) {
            $pos= array_search($project, $this->project_index);
            $raw_tasks = array_insert($raw_tasks, $pos, $new_raw_tasks, 1, true);
        } else {
            $raw_tasks = $new_raw_tasks;
        }
    }

    /******************************************
     * Local function
     */
    private function _to_cache() {
        $cache = & $this->_cache;
        $cache['plain_text'] = $this->plain_text;
        $cache['raw_tasks'] = $this->raw_tasks;
        $cache['item_type'] = $this->item_type;
        $cache['parsed_tasks'] = $this->parsed_tasks;
        $cache['task_count'] = $this->task_count;
        $cache['all_tags'] = $this->all_tags;
        $cache['tag_count'] = $this->tag_count;
        $cache['tag_freq'] = $this->tag_freq;
        $cache['all_projects'] = $this->all_projects;
        $cache['project_count'] = $this->project_count;
        $cache['project_index'] = $this->project_index;
        $cache['task_project'] = $this->task_project;
        $cache['task_date'] = $this->task_date;
        $cache['task_state'] = $this->task_state;
        $cache['file_path'] = $this->file_path;
        $cache['file_name'] = $this->file_name;
        file_put_contents($this->_cache_path, serialize($cache));
    }
    private function _from_cache() {
        $cache = unserialize(file_get_contents($this->_cache_path));
        $this->plain_text = $cache['plain_text'];
        $this->raw_tasks = $cache['raw_tasks'];
        $this->item_type = $cache['item_type'];
        $this->parsed_tasks = $cache['parsed_tasks'];
        $this->task_count = $cache['task_count'];
        $this->all_tags = $cache['all_tags'];
        $this->tag_count = $cache['tag_count'];
        $this->tag_freq = $cache['tag_freq'];
        $this->all_projects = $cache['all_projects'];
        $this->project_count = $cache['project_count'];
        $this->project_index = $cache['project_index'];
        $this->task_project = $cache['task_project'];
        $this->task_date = $cache['task_date'];
        $this->task_state = $cache['task_state'];
        $this->file_path = $cache['file_path'];
        $this->file_name = $cache['file_name'];
    }
    /**
     * opposite of function below: builds plain text string (from raw_task cache)
     * Used by save_edits(EDIT_STATE) mainly
     */
    private function _build_plain_text($raw_tasks) {
        $plain_text = current($raw_tasks) . "\n";
        while (next($raw_tasks)) {
            $item_type = $this->item_type[key($raw_tasks)];
            if ($item_type == ITEM_PROJ || $item_type == ITEM_LABEL) {
                $plain_text .= "\n";
            }
            $plain_text .= current($raw_tasks) . "\n";
        }
        return $plain_text;
    }
    /**
     * Build all the cached lists: tasks, tags, projects (from plain_text)
     * plus: project_index => index locations of project header lines
     * plus: task_project => which project this task belongs to
     */
    private function _build_task_lists($plain_text) {
        // clear existing task data
        $this->raw_tasks = array();     // line no. => raw plain text of task
        $this->item_type = array();     // line no. => item type for line (task, project, label)
        $this->parsed_tasks = array();  // line no. => parsed task object

        $this->task_project = array();  // line no. => project idx no. for this task
        $this->task_date = array();     // line no. => task date
        $this->task_state = array();    // line no. => task's state

        $this->project_index = array(); // line no.(project line no)  => project idx no.
        $this->all_projects = array();  // project idx  => project name
        $this->all_tags = array();      // tag idx      => tag name
        $this->tag_freq = array();      // freq counts for tags

        $this->all_projects[0] = lang('projectless');   // first project is for orphaned tasks...

        $all_lines = explode("\n", $plain_text);
        $plain_text = '';
        $project_idx = 1;
        $cur_project_idx = 0;
        $line_idx = -1;
        $task_idx = -1;
        $in_block = false;
        $block_note = '';
        $done_state = MAX_ACTION + 1;
        $cur_task = new ParsedTask();

        // extra locals to avoid calling config on each loop! [optimisation based on Cachegrind results]
        $note_prefix = config('note_prefix');
        $note_rgx = config('note_rgx');
        $task_rgx = config('task_rgx');
        $project_rgx = config('project_rgx');
        $label_prefix = config('label_prefix');

        // NOTE: "numeric" STRING keys are used for the task list, easier for sorting, inserting and creating unique merges
        $max = count($all_lines) - 1;
        $cnt = 0;
        $pre = $post = '';
        foreach ($all_lines as $line) {
            $line = trim($line);

            // start|end of a block note
            if ($task_idx >= 0 && $line == $note_prefix || $in_block && $cnt == $max) {
                $in_block = !$in_block;
                $this->raw_tasks['0' . $line_idx] .= "\n" . $line;
                if (!$in_block) {
                    $cur_task->notes[] = new Note($block_note, BLOCK_NOTE);
                    $block_note = '';
                }

            // blocknotes: all other syntaxes are ignored inside, incl. blank lines
			} elseif ($in_block) {
                $this->raw_tasks['0' . $line_idx] .= "\n" . $line;
                $block_note .= (empty($block_note)) ? $line : "\n" . $line;

            // single line notes
            } elseif ($task_idx >= 0 && preg_match($note_rgx, $line, $matches) > 0) {
                // all notes belong to previous task (if there is one...)
                $this->raw_tasks['0' . $line_idx] .= "\n" . $line;
                $cur_task->notes[] = new Note($matches[1], SINGLE_NOTE);
                $in_block = false;

            } elseif (preg_match($task_rgx, $line) > 0) {
                $line_idx++;
                $task_idx++;
                // collect the task, both raw and parsed
                $line_key = '0' . $line_idx;
                $this->raw_tasks[$line_key] = $line;
                $this->item_type[$line_key] = ITEM_TASK;
                $this->parsed_tasks[$line_key] = $this->_parse_task($line);
                $cur_task = & $this->parsed_tasks[$line_key];

                // also create searching|sorting arrays
                $this->task_date[$line_key] = $cur_task->date;
                $this->task_state[$line_key] = ($cur_task->done === true) ? $done_state : $cur_task->action;
                $this->task_project[$line_key] = $cur_project_idx;

                // keep adding all tags found (a unique list will be created later)
                if ( ! empty($cur_task->tags)) {
                    $this->all_tags = array_merge($this->all_tags, $cur_task->tags);
                }
                $in_block = false;

            } elseif (preg_match($project_rgx, $line, $match) > 0) {
                $line_idx++;
                // If a topic was the first line then there were no 'topic-less' tasks
                if ($task_idx == -1) {
                    $this->all_projects[0] = '';
                }
                if ($line_idx > 0) {
                    $pre= "\n";
                }
                $project_key = '0' . $line_idx;
                $this->raw_tasks[$project_key] = $cur_project = $line;
                $this->item_type[$project_key] = ITEM_PROJ;
                $this->all_projects[$project_idx] = $match[1];
                $this->project_index[$project_key] = $project_idx;
                $cur_project_idx = $project_idx++;
                $in_block = false;
            // everything else is viewed as a descriptive "label"
            } elseif ( ! empty($line)) {
                $line_idx++;
                $line_key = '0' . $line_idx;
                $this->raw_tasks[$line_key] .= $line;
                $this->item_type[$line_key] = ITEM_LABEL;
                $in_block = false;
                if ($line_idx > 0) $pre= "\n";
                $post = "\n";
            }
            if ( ! empty($line)) $plain_text .= $pre . $line . "\n" . $post;
            $cnt++;
            $pre = $post = '';
        }
        $this->plain_text = str_replace("\n\n\n", "\n\n", $plain_text);

        // get a list of all unique tags
        $this->all_tags = preg_grep(config('date_rgx'), $this->all_tags, PREG_GREP_INVERT);
        natcasesort($this->all_tags);
        $this->all_tags = array_count_values($this->all_tags);
        //$this->all_tags = array_unique($this->all_tags);    //without the @ prefix: for display only!

        // cache the various list counts to avoid further lookup
        $this->task_count = $task_idx + 1;
        $this->project_count = $project_idx;
        $this->tag_count = count($this->all_tags);
    }

    /**
     * Parse a given task into its elements
     * NOTE: when a task is displayed, the 'done' state will take priority,
     *       although all state information is parsed and stored
     * @param string $task  raw task
     * @return array of task tokens by name (done, text, tags, state, date)
     */
    private function _parse_task($task) {
        static $rgx = array();
        // cache the regexes to save microseconds on each task build
        if (empty($rgx)) {
            $rgx['action_rgx'] = config('action_rgx');
            $rgx['task_prefix'] = config('task_prefix');
            $rgx['done_prefix'] = config('done_prefix');
            $rgx['date_tag_rgx'] = config('date_tag_rgx');
            $rgx['tag_rgx'] = config('tag_rgx');
            $rgx['tag_prefix'] = config('tag_prefix');
        }
        // defaults for things that could be missing
        $date = 0;
        $tags = array();
        $text = $task;

        // get task action: 0. none, 1.next, 2.wait, 3.maybe (can be customised in config.php)
        $done = false;
        $action = 0;
        if (preg_match($rgx['action_rgx'], $task, $matches) == 1) {
            $action = mb_strlen($matches[1]);
            $text = mb_substr($text, 0, -$action);
            if ($action > MAX_ACTION) $action = MAX_ACTION;
        }
        $done_len = mb_strlen($rgx['task_prefix']);
        if (mb_substr($text, 0, 1) == $rgx['done_prefix']) {
            $done = true;
            $text = mb_substr($text, $done_len + 1);
        } else {
            $text = mb_substr($text, 1);
        }

        // convert any =interval tags into real tasks
        $text = $this->expand_interval_tags($text);

        if (preg_match($rgx['date_tag_rgx'], $text, $matches) == 1) {
            $date = strtotime($matches[1]);
        }
        if (preg_match_all($rgx['tag_rgx'], $text, $matches) >= 1) {
            $tags = $matches[1];
        }
        $text = preg_replace($rgx['tag_rgx'], '', $text);   //remove the tags!
        // NOTE: notes are added whilst building the cached task list (they are on following lines)
        return new ParsedTask($text, $done, $tags, $action, $date);
    }
}

/**
 * A raw task parsed into its composite elements
 */
class ParsedTask {
    function __construct($text = '', $done = false, $tags = array(), $action = 0, $date = '', $notes = array()) {
        $this->text = $text;
        $this->done = $done;
        $this->tags = $tags;
        $this->action = $action;
        $this->date = $date;
        $this->notes = $notes;
    }
}
?>
