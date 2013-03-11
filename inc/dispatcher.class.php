<?php
namespace tpp\control;
use tpp, tpp\User\State as State;


/**
 * Basic router/dispatcher, based on calling correct function by naming convention.
 *
 * @author syanna
 */
class BasicDispatcher {

    const PFX_ACTION = 'action_';
    const PFX_STATE = 'state_';

    const FAILED = '__failed__';
    const ACTION = '__action__';
    const UPDATED = '__updated__';
    const NEWSTATE = '__newstate__';

    public $request;
    public $state;

    /**
     * @param \tpp\control\State $state Current active Tab/view State
     * @param array $default    Default request parameters
     */
    function __construct(State $state, Array $defaults) {
        $this->state = $state;
        $this->request = new Request($defaults);
    }

    /**
     * Main response function: all page requests pass through here.
     *
     * The request is dispatched to the correct PHP function based on a simple naming convention: either as an INDEX (i.e. page load), ACTION or a STATE
     *
     * Html/Json response is send directly to browser
     */
    function respond() {

        $request = & $this->request;
        $response = false;

        // first check for page refresh or first load
        if ($this->request->source == REQ_INDEX) {
            $state = $request->to_state(true);
            $response = $this->_do_index($state);

        } else {

            // next try for action event
            if ( ! $response) {
                $response = $this->_do_action($request);
            }

            // then fall back to state event
            if ( ! $response) {
                $state = $request->to_state();
                $response = $this->_do_state($state);
            }
        }

        // send response content to browser
        if ($response !== false) {
            if ($request->source == REQ_AJAX) {
                header('Content-type: application/json', true, 200);
                $response = json_encode($response);
            } else {
                header('Content-type: text/html; charset=utf-8');
            }
            echo $response;
            return true;
        } else {
            return false;
        }
    }

    private function _do_index(State $state) {

        log&&msg(__METHOD__, 'found index EVENT, state: ', $state);

        $response = $this->index($state);
        $state->activate();
        return $response;
    }

    /**
     * ACTIONS: carry out an action, but do not return a new page state.
     * Only Ajax requests can do an action!
     *
     * @param \tpp\control\Request $request
     * @return boolean
     */
    private function _do_action(Request $request) {

        $ajax = $request->source == REQ_AJAX;
        $has_action = ! is_null($request->event);
        $action_exists = method_exists($this, self::PFX_ACTION . $request->event);
        if ($ajax && $has_action && $action_exists) {
            $response = call_user_func(array($this, self::PFX_ACTION . $request->event), $request);

            log&&msg(__METHOD__, 'found ACTION from: ', $request);

            // only content updated, return previous STATE
            if ($response == self::UPDATED) {
                $response = $this->_do_state($this->state);

            // internal action, no visible changes
            } elseif ($response == self::ACTION) {
                $response =  array('type' => self::ACTION);

            // this action led to another STATE
            } elseif ($response instanceof State) {
                $response = $this->_do_state($response);

            // failure!
            } elseif ($response === false
                        || empty($response)
                        || $response == self::FAILED) {
                $response =  array('type' => self::FAILED);
            }
            return $response;
        }
        return false;
    }

    /**
     * STATE: Create and set the final page state (address).
     *
     * @param \tpp\control\State $state
     */
    private function _do_state(State $state = null) {

        if (method_exists($this, self::PFX_STATE . $state->event)) {

            log&&msg(__METHOD__, 'found state EVENT, setting state to: ', $state);

            $response = call_user_func(array($this, self::PFX_STATE . $state->event), $state);

            if ($response !== false) {
                $response['event'] = $state->event;
                $response['address'] = $state->to_address();

                // this state becomes the new Active
                $state->activate();

                log&&msg(__METHOD__, 'called STATE:', $state, 'RESPONSE is: ', $response);
                return $response;
            }
        }
        return false;
    }
}



class Dispatcher extends BasicDispatcher {

    protected $_taskpapers;
    protected $_taskpaper;
    protected $_views;
    protected $_states;
    protected $_files;
    protected $_cache;

    const TAB_NONE      = 0;
    const TAB_SAME      = 1;
    const TAB_CHANGED   = 2;
    const TAB_NEW       = 3;

    function __construct(tpp\App $app) {

        log&&msg(__METHOD__, 'constructing Dispatcher');

        $this->_states = $app->states;
        $this->_taskpapers = $app->taskpapers;
        $this->_taskpaper = $app->taskpapers->active();
        $this->_views = $app->views;
        $this->_files = $app->files;
        $this->_user = $app->user;
        $this->_cache = $app->cache;

        $this->state = $app->states->active();

        // defaults for missing request params
        // (needed later to ensure a valid state)
        $default = array();
        $default['tab']  = $this->state->tab;
        $default['event'] = DEFAULT_EVENT;
        $default['value'] = DEFAULT_VALUE;
        $default['draft'] = null;

        parent::__construct($this->state, $default);
    }

    function respond() {

        // first confirm that user is valid
        if ( ! $this->_check_login()) {

            log&&msg(__METHOD__, 'login failed');

            return;
        }

        $request = & $this->request;

        log&&msg(__METHOD__, 'beginning the response; request is:', $request);


        // save any draft text...
        if ($this->state->event == 'edit' && ! empty($this->request->draft)) {
            $this->state->draft = $request->draft;
            $this->state->save();
        }

        if ($request->source == REQ_INDEX) {
            $request->tab = $this->state->tab;
        }
        parent::respond();

        log&&msg(__METHOD__, 'finished response, state saved as:', $this->_states->active());
    }


    protected function _check_login() {
        if ( ! $this->_user->logged_in()) {
            // show login screen
        }
        // TODO: finish login screens!
        return true;
    }


    /**
     * Main start up Index page: full rebuild back to default view.
     *
     * @param State $state
     */
    protected function index(State $state) {
        $this->_change_tab($state->tab);
        $address = $state->to_address();
        $view = $this->_views->index($address);
        return $view;
    }


    // **********************************
    // STATES: returns a new page address
    // **********************************

    protected function state_all() {
        return $this->_views->all_json();
    }

    protected function state_search(State $state) {
        if ( ! empty($state->value)) {
            $result = $this->_taskpaper->search()->by_expression($state->value);
            return $this->_views->results_json($result);
        } else {
            return false;
        }
    }

    protected function state_filter(State $state) {
        $result = $this->_taskpaper->search()->by_named_filter($state->value);
        return $this->_views->results_json($result);
    }

    protected function state_tag(State $state) {
        $result = $this->_taskpaper->search()->by_tag($state->value);
        return $this->_views->results_json($result);
    }

    /**
     * Returns a Project state, which is referenced by *index* number not key.
     * This allows Projects to be saved as links.
     */
    protected function state_project(State $state) {
        return $this->_views->project_json($state->value);
    }

    protected function state_edit(State $state) {
        $text = ( ! empty($state->draft)) ? $state->draft : $this->_taskpaper->raw();
        return $this->_views->edit_json($text);
    }


    // *****************************************
    // ACTION ONLY requests, no new page address
    // *****************************************

    protected function action_add() {
        global $term;

        // Where in task list to add the task?
        $project_index = 0;
        $task_added = false;
        $task = $this->request->value;

        $matched = preg_match($term['add_to_proj'], $task, $matches);
        if ($matched !== false && $matched > 0) {
            $project_index = $matches[1];
            // TODO: this is rubbish--no check for symbol!  What if * at end?
            // remove the project number from end (everything after last space)
            $task = mb_substr($task, 0, strrpos($task, ' ', -1));

        } elseif ($this->state->event == 'project') {
            $project_index = $this->state->value;
        }

        if ($project_index >= 0) {
            $task_added = $this->_taskpaper->add($task, $project_index);
        } else {
            $task_added = $this->_taskpaper->add($task);
        }

        return ($task_added ? self::UPDATED : false);
    }

    protected function action_sort() {
        if ($this->state->event == 'project') {
            $project = $this->state->value;
        } else {
            $project = null;
        }
        $this->_taskpaper->reorder($this->request->value, $project);
        return self::UPDATED;
    }

    protected function action_save() {
        $this->_taskpaper->update(UPDATE_RAW, $this->request->value);
        // saving true reverts to default state, removing old draft text
        $state = $this->request->to_state(true);
        return $state;
    }

    protected function action_done() {
        $task = $this->_taskpaper->items[$this->request->value];
        $task->done( ! $task->done());
        return self::UPDATED;
    }

    protected function action_action() {
        $task = $this->_taskpaper->items[$this->request->value];
        $task->action($task->action() + 1);
        return self::UPDATED;
    }

    protected function action_remove_actions() {
        $this->_taskpaper->remove_actions();
        return self::UPDATED;
    }

    protected function action_archive_done() {
        $this->_taskpaper->archive_done();
        return self::UPDATED;
    }

    protected function action_trash_done() {
        $this->_taskpaper->trash_done();
        return self::UPDATED;
    }

    protected function action_archive() {
        $this->_taskpaper->archive($this->request->value);
        return self::UPDATED;
    }

    protected function action_trash() {
        $this->_taskpaper->trash($this->request->value);
        return self::UPDATED;
    }

    protected function action_rename() {
        $name = $this->_taskpapers->rename($this->request->value);
        $this->state->tab = $name;
        return self::UPDATED;
    }

    protected function action_remove() {
        $name = $this->_taskpapers->delete();
        $this->_taskpaper = $this->_taskpapers->active($name);
        $state = $this->_states->item($name);
        return $state;
    }

    protected function action_editable() {
        if ( ! empty($this->request->key)) {
            $key = $this->request->key;
            $text = $this->request->value;
            $items = $this->_taskpaper->items();
            $items[$key]->raw($text);
            return self::UPDATED;
        }
        return false;
    }


    protected function action_purgesession() {
        $this->_states->clear();
        return self::ACTION;
    }

    protected function action_purgecache() {
        $this->_cache->purge();
        return self::ACTION;
    }

    protected function action_lang() {
        ini('language', $this->request->value);
        return self::ACTION;
    }

    /**
     * Show a specific tab state. Always returns a new state.
     *
     * Used by url, change tab, and new tab requests.
     *
     * @param Request $request
     */
    protected function action_show() {

        $tab = $this->request->tab;
        $text = $this->request->value;

        $what_changed = $this->_change_tab($tab, $text);

        switch ($what_changed) {
            case self::TAB_SAME:
                $state = $this->request->to_state(true);

                log&&msg(__METHOD__, 'refresh tab: leave state as:', $state);

                break;

            case self::TAB_CHANGED:
                $state = $this->_states->item($tab);

                log&&msg(__METHOD__, 'change tab: setting state to:', $state);

                break;

            case self::TAB_NEW:
                $state = $this->request->to_state();

                // which default edit state?
                $state->event = tpp\config('edit_new_tab') ? 'edit' : 'all';
                if ($state->event == 'all') $state->value = ''; // edit event set the draft text as a value

                log&&msg(__METHOD__, 'new tab: setting state to:', $state);

                break;

            default:
                $state = false;
        }
        return $state;
    }


    private function _change_tab($name, $text = null) {

        if ($this->_taskpapers->exists($name)) {

            if ($name == $this->state->tab) {
                return self::TAB_SAME;

            } else {
                $this->_taskpaper = $this->_taskpapers->active($name);
                return self::TAB_CHANGED;
            }

        } else {
            $new_tab = $this->_taskpapers->create($name, $text);
            if ($new_tab !== false) {
                $this->_taskpaper = $this->_taskpapers->active($new_tab);
                return self::TAB_NEW;
            }
        }
        return self::TAB_NONE;
    }
}

?>
