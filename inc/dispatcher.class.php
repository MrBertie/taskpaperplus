<?php
require_once(APP_PATH . 'inc/request.class.php');

class Dispatcher {

    private $_taskpapers;
    private $_active_taskpaper;
    private $_view;
    private $_tab_state;
    private $_files;
    private $_fetch_tabs;
    private $_state = null;

    const PFX_ACTION = 'action_';
    const PFX_STATE = 'state_';

    const TAB_SAME = 0;
    const TAB_DIFF = 1;
    const TAB_NEW = 2;

    const STATE_NONE = 0;
    const STATE_CURRENT = 2;
    const STATE_OTHER = 3;

    function __construct(Taskpapers $taskpapers, TabState $tab_state, View $view, Files $files) {
        log&&msg(__METHOD__, 'constructing Dispatcher');
        $this->_tab_state = $tab_state;
        $this->_state = $tab_state->state();  // can be changed by states|actions
        $this->_taskpapers = $taskpapers;
        $this->_active_taskpaper = $taskpapers->active();
        $this->_view = $view;
        $this->_files = $files;
        $this->_fetch_tabs = false;
    }
    /**
     * Main response function: all AJAX/URL and internal requests pass through here!
     * The request is dispatched to the correct PHP function based on a simple naming convention
     *
     * @return State   $state    the new state (html response is send directly to browser)
     */
    function respond() {

        $request = $this->_get_request();
        log&&msg(__METHOD__, 'beginning the response; request is:', $request);

        // save any draft text
        // saved as the current value if state is 'edit'
        if ( ! empty($request->draft) && $this->_state->event = 'edit') {
            $this->_tab_state->state()->value = $request->draft;
            $this->_tab_state->save();
        }

        if ($request->type == REQ_INDEX) {
            $this->index($this->_state);

        } else {
            if ($request->type == REQ_URL) {
                $tab = $request->tab;  // always has a value
                $event = $request->event;
                $value = $request->value;

                /* first deal with tab names
                 * which sometimes need to be converted internally into actions or states
                 */

                // different tab
                if ($this->_taskpapers->exists($tab)) {
                    $request->event = 'changetab';
                    $request->value = "$tab/$event/$value";
                    $request->type = REQ_AJAX;  // convert to internal ajax event now

                // new tab
                } elseif ( ! $this->_taskpapers->exists($tab)) {
                    $request->event = 'newtab';
                    $request->value = "$tab/$event/$value";
                    $request->type = REQ_AJAX;  // convert to internal ajax event now

                // if not same tab then nothing matches: just bail out!
                } elseif ($tab != $this->_state->tab)  {
                    return;
                }
            }

            /* event response */

            // action event
            if ($this->_do_action($request) === false) {

                // assume state event
                $this->_set_state($request->to_state());
            }
        }
    }

     /**
     * assumes that each POST or GET request will
     * include 'tab', 'action', 'value' fields, identifying the
     * requesting 'action' in js
     * A missing request will result in a page rebuild
     * @return array
     */
    private function _get_request() {

        $request = array();
        if ( ! empty($_POST)) {
            $request = &$_POST;
        } elseif ( ! empty($_GET)) {
            $request = &$_GET;
        }
        $tab = isset($request['tab']) ? $request['tab'] : '';
        $event = isset($request['event']) ? $request['event'] : '';
        $value = isset($request['value']) ? $request['value'] : '';
        $draft = isset($request['draft']) ? $request['draft'] : null;

        if (empty($tab) && empty($event)) {
            $type = REQ_INDEX;
            $tab = $this->_state->tab;
            $value = '';
            $draft = null;
        } else {
            if (empty($event)) $event = 'all';
            if( ! empty($tab)) {
                $type = REQ_URL;
            } else {
                $type = REQ_AJAX;
                $tab = $this->_state->tab;
            }
        }

        return new Request($type, $tab, $event, $value, $draft);
    }


    private function _do_action($request) {
        if (method_exists($this, self::PFX_ACTION . $request->event)) {
            // no actions allowed for external URLs!
            if ($request->type != REQ_URL) {
                $response = call_user_func(array($this, self::PFX_ACTION . $request->event), $request->value);
                if ($response === true) {
                    $this->_set_state($this->_state);
                } elseif ($response === false) {
                    echo json_encode('__failed__');
                } elseif (empty($response)) {
                    echo json_encode('__action__');
                } else {
                    $this->_set_state($response);
                }
                log&&msg(__METHOD__, 'found action EVENT, calling: ', $request);
                return true;
            }
        }
        return false;
    }
    /**
     * Create and set the final state
     */
    private function _set_state(State $state) {
        // is it a valid STATE? call correct function; return response must be an array
        if (method_exists($this, self::PFX_STATE . $state->event)) {
            log&&msg(__METHOD__, 'found state EVENT, setting state to: ', $request);
            $response = call_user_func(array($this, self::PFX_STATE . $state->event), $state->value, &$state);
            if ($response !== false) {
                $response['event'] = $state->event;
                $response['address'] = $state->address();
                if ($this->_fetch_tabs) {
                    $response = array_merge($response, $this->_fetch_tabs());
                }
                // send the new data to the browser
                echo json_encode($response);
                // save the new page state
                $this->_tab_state->state($state);
                log&&msg(__METHOD__, 'Disp: sending response to browser, state save as:', $state, ', event as:' . $response['event']);
            }
        }
    }

    private function index(State $state) {
        // index: full page rebuild
        $state->event = 'all';
        $state->value = '';
        $state->draft = '';
        log&&msg(__METHOD__, 'preparing to build a new index page:', $state->tab);
        $view = $this->_view->index($state->address());
        $this->_tab_state->state($state);
        echo $view->fetch();
    }

    // ************** STATES: => ACTIONS with new page address *******************

    private function state_all() {
        return $this->_fetch_all();
    }
    private function state_search($expression) {
        if ( ! empty($expression)) {
            $result = $this->_active_taskpaper->search()->by_expression($expression);
            return $this->_fetch_results($result);
        } else {
            return false;
        }
    }
    private function state_filter($name) {
        $result = $this->_active_taskpaper->search()->by_named_filter($name);
        return $this->_fetch_results($result);
    }
    private function state_tag($tag) {
        $result = $this->_active_taskpaper->search()->by_tag($tag);
        return $this->_fetch_results($result);
    }
    private function state_project($key, State $state) {
		// convert project key to order number
        if (strlen($key) > 1 && $key[0] == '0') {
            $project_num = $this->_active_taskpaper->content->proj_num($key);
        } else {
            $project_num = $key;
        }
        $state->value = $project_num;   // update state's value to match actual project number!
        return $this->_fetch_project($project_num);
    }
    private function state_edit($draft = '') {
        if ( ! empty($draft)) {
            $text = $draft;
        } else {
            $text = $this->_active_taskpaper->plain_text();
        }
        return array('text' => $text) + $this->_fetch_sidebars();
    }

    // ********** ACTION ONLY requests, no new page address *************
    private function action_add($task) {
        // Where in task list to add the task?
        $project_num = 0;
        $task_added = false;
        $has_project = preg_match(config('in_proj_rgx'), $task, $matches);
        if($has_project !== false && $has_project > 0) {
            $project_num = $matches[1];
            $task = mb_substr($task, 0, strrpos($task, ' ', -1));  // remove the project number from end (after last space)
        } elseif ($this->_state->event == 'project') {
            $project_num = $this->_state->value;
        }
        if ($project_num > 0) {
            $task_added = $this->_active_taskpaper->tasks()->add($task, $project_num);
        } else {
            $task_added = $this->_active_taskpaper->tasks()->add($task);
        }
        return $task_added;
    }
    private function action_sort($order) {
        if ($this->_state->event == 'project') {
            $project = $this->_state->value;
        } else {
            $project = null;
        }
        $this->_active_taskpaper->content->reorder($order, $project);
        $this->_active_taskpaper->save_edits(EDIT_CACHE);
        return true;
    }
    private function action_save($edited_tasks) {
        $this->_active_taskpaper->save_edits(EDIT_PLAINTEXT, $edited_tasks);
        $this->_state->event = 'all';
        $this->_state->value = '';  // remove old draft text
        return $this->_state;
    }
    private function action_done($key) {
        $task = $this->_active_taskpaper->tasks()->item($key);
        $task->done(!$task->done());
        return true;
    }
    private function action_action($key) {
        $task = $this->_active_taskpaper->tasks()->item($key);
        $task->action($task->action() + 1);
        return true;
    }
    private function action_noactions() {
        $this->_active_taskpaper->tasks()->no_actions();
        return true;
    }
    private function action_archiveall() {
        $this->_active_taskpaper->tasks()->archive_all();
        return true;
    }
    private function action_archive($key) {
        $this->_active_taskpaper->tasks()->archive($key);
        return true;
    }
    private function action_erase($key) {
        $this->_active_taskpaper->tasks()->delete($key);
        return true;
    }
    private function action_rename($new_name) {
        $name = $this->_taskpapers->rename($new_name);
        $this->_state->tab = $name;
        $this->_fetch_tabs = true;
        return true;
    }
    private function action_remove() {
        $current = $this->_taskpapers->delete();
        $this->_fetch_tabs = true;
        $state = $this->_tab_state->fetch_state($current);
        $this->_active_taskpaper = $this->_taskpapers->active($current);
        return $state;
    }
    private function action_editable($edited_task) {
        list($key, $text) = explode(":", $edited_task, 2);
        if ($key != '') {
            $this->_active_taskpaper->tasks()->item($key)->plain($text);
            return true;
        }
        return false;
    }
    /**
     * Purge the session data
     */
    private function action_purgesession() {
        $this->_tab_state->clear();
    }
    private function action_purgecache() {
        $this->_files->purge_cache();
    }
    private function action_lang($lang) {
        ini('language', $lang);
    }
    private function action_newtab($tab_event_value) {
        list($tab, $event, $value) = $this->_safe_explode($tab_event_value);
        $new_tab = $this->_taskpapers->create($tab);
        if ($new_tab !== false) {
            if ($event != 'all' && $event != 'edit') {
                $event = config('edit_new_tab') ? 'edit' : 'all';
            }
            if ($event != 'edit') $value = '';
            $this->_active_taskpaper = $this->_taskpapers->active($new_tab);
            $this->_fetch_tabs = true;
            $state = new State($new_tab, $event, $value);   // 'edit' states can set a text value!
            log&&msg(__METHOD__, 'newtab: setting state to:', $state);
            return $state;
        }
        return false;
    }
    private function action_changetab($tab_event_value) {
        list($tab, $event, $value) = $this->_safe_explode($tab_event_value);
        if ($tab == $this->_state->tab) {
            $event = 'all';
            $value = '';
        } else {
            $this->_active_taskpaper = $this->_taskpapers->active($tab);
        }
        if (empty($event)) {
            // go back to previous state for this tab (never an action!)
            $this->_fetch_tabs = true;
            $prev_state = $this->_tab_state->fetch_state($tab);
            log&&msg(__METHOD__, 'changetab: found empty request, setting state to:', $prev_state);
            return $prev_state;
        } else {
            $this->_fetch_tabs = true;
            $state = new State($tab, $event, $value);
            log&&msg(__METHOD__, 'changetab: found request: "$tab_event_value", setting state to:', $state);
            return $state;
        }
        return false;
    }

    private function _safe_explode($tab_event_value) {
        $tab = $event = $value = '';
        $args = explode('/', $tab_event_value);
        if (isset($args[0])) $tab = $args[0];
        if (isset($args[1])) $event = $args[1];
        if (isset($args[2])) $value= $args[2];
        return array($tab, $event, $value);
    }

    /**
     * convenience functions: fetch various page views
     */

    private function _fetch_tabs() {
        return array('tabs' => $this->_view->tabs()->fetch());
    }
    private function _fetch_all() {
        return array('tasks' => $this->_view->all()->fetch()) + $this->_fetch_sidebars();
    }
    // search results and project also returned as 'tasks'--easier to return to previous this way
    private function _fetch_project($project) {
        return array('tasks' => $this->_view->project($project)->fetch()) + $this->_fetch_sidebars();
    }
    private function _fetch_results($result) {
        return array('tasks' => $this->_view->results($result)->fetch()) + $this->_fetch_sidebars();
    }
    private function _fetch_sidebars() {
        return array('projects' => $this->_view->projects()->fetch(),
                      'tags' => $this->_view->tags()->fetch());
    }
}
?>