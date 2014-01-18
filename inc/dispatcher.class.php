<?php
namespace tpp\control;
use tpp, tpp\user\Address as Address;


class Dispatcher extends DispatcherBase {
    
    private $app;
    private $request;
    private $mapping;
    
    const STATEFUL = '_state';

    
    /**
     * @param tpp\App $app An instance of the web-app class, for use by the dispatch functions
     */
    function __construct(tpp\App $app) {
        $this->app     = $app;
        $this->request = new Request();
        // the $mapping defines the order in which the request parameters
        // should be passed to the handler functions
        $this->mapping = array(
            'action' => DEFAULT_ACTION,
            'tab'    => $app->state()->address->tab,
            'key'    => '',
            'value'  => DEFAULT_VALUE,
            'draft'  => null,
        );
    }
    
    
    /**
     * Start the dispatcher.
     * 
     * @return boolean
     */
    function start() {
        if ($this->before()) {
            return $this->route($this->request);
        } else {
            return false;
        }
    }
    
    
    /**
     * Tee main routing function, all routing passes through here!
     * 
     * Request: Http request routing, mapped to sequence of args as for states.
     * State:   The current tab name is passed to stateful route functions
     *          Used for constructing the final page address.
     *          Stateful functions also have '_state' appended to end of function name
     * None:    Defaults to current state
     * Args:    Array consisting of:  tab, action, value (as per address!)
     *          Used for manually setting page state (e.g. via the url bar).
     *          These should always be stateful.
     *          Converts from tab,action,value (url hash) to action, tab, value (internal call)
     * Tab:     New tab name only, therefore try to get action and value from previous state of this tab
     * 
     * @param mixed $dest Either null, State, Request, array of args, or string (tab name)
     * @return boolean On success or failure
     */
    protected function route($dest = null) {
        $tab = '';
        $is_action = false;
        
        \log&&msg('Current state is:', $this->app->state()->address);
        
        // Request
        if ($dest instanceof Request) {
            $request = $dest;
            // special index page build route
            if ($request->source == REQ_INDEX) {
                // a couple of little dev parameters, useful for resetting
                // when things get pear shaped
                if (isset($request->purge_session)) {
                    $this->purgesession();
                    return;
                } elseif (isset($request->purge_cache)) {
                    $this->purgecache();
                    return;
                }
                $this->index();
                return true;
            } else {
                $args = $this->_map_request($request, $this->mapping);
                if (method_exists($this, $args[0] . self::STATEFUL)) {
                    $func = array_shift($args);
                    $tab  = (isset($request->tab)) ?
                            $request->tab :
                            $this->app->state()->address->tab;
                    $args = array_merge((array) $func, (array) $tab, $args);
                } else {
                    $is_action = true;
                }
            }
        
        // State
        } elseif ($dest instanceof Address) {
            $address = $dest;
            $args    = array($address->action,
                             $address->tab,
                             $address->value);
            
        // None
        } elseif ($dest === null) {
            $address = $this->app->state()->address;
            $args    = array($address->action,
                             $address->tab,
                             $address->value);
        
        // Manual args
        } elseif (is_array($dest)) {
            $args = array($dest[1], $dest[0], $dest[2]);
        
        // Just a tab name only
        } elseif (is_string($dest)) {
            $tab     = $dest;
            $address = $this->app->states->get($tab)->address;
            $args    = array($address->action, $tab, $address->value);
        
        // Abject failure...
        } else {
            
            \log&&msg('No route matched.  Destination was: ', $dest);
            
            return false;
        }
        if ( ! $is_action) {
            $args[0] .= self::STATEFUL;
        }
        
        \log&&msg('Destination was:', $dest, "\tRouting to:", $args);
        
        return parent::route($args);
    }


    // Abstract hook functions that **MUST** be overridden


    protected function before() {
        // first confirm is user is logged in
        $logged_in = $this->app->user->do_login();
        return $logged_in;
    }


    protected function before_route(&$func, &$args) {
        // if user was in an 'edit' state then save any draft text to current state
        $state = $this->app->state();
        if ($state->address->action == 'edit' && ! empty($this->request->draft)) {
            $state->draft = $this->request->draft;
            $state->save();
        }
    }
    
    
    protected function before_html_response(Array &$response) {}
    
    
    protected function before_json_response(Array &$response) {}
    
    
    protected function after_response(Array &$response, Address $address = null) {
        // the state of all address type responses are saved by default
        if ( ! empty($address)) {
            $this->app->states->set($address)->save();
        }
        
        log&&msg('JSON response was:', $response, 'Setting state address to:', $address);
    }

    
    // ****************************************
    // INDEX: inital page load
    // ****************************************
    

    function index() {
        $address = $this->app->state()->reset()->address;
        $this->app->taskpapers->set($address->tab);
        $view    = $this->app->views->index(strval($address));
        $this->respond(eRespType::INDEX, $view, $address);
    }

    
    // *****************************************
    // STATEFUL requests, => new page address
    // *****************************************

    function all_state($tab) {
        $view    = $this->app->views->all()->json('tasks') +
                   $this->app->views->meta();
        $this->respond(eRespType::ADDRESS, $view, new Address($tab));
    }

    function search_state($tab, $expression) {
        $view = $this->app->views->search($expression)->json('tasks') + 
                $this->app->views->meta();
        $this->respond(eRespType::ADDRESS, $view, new Address($tab, 'search', $expression));
    }
    
    function filter_state($tab, $filter) {
        $view = $this->app->views->filter($filter)->json('tasks') + 
                $this->app->views->meta();
        $this->respond(eRespType::ADDRESS, $view, new Address($tab, 'filter', $filter));
    }

    function tag_state($tab, $tag) {
        $view = $this->app->views->tag($tag)->json('tasks') + 
                $this->app->views->meta();
        $this->respond(eRespType::ADDRESS, $view, new Address($tab, 'tag', $tag));
    }

    function project_state($tab, $project) {
        $view = $this->app->views->project($project)->json('tasks') + 
                $this->app->views->meta();
        $this->respond(eRespType::ADDRESS, $view, new Address($tab, 'project', $project));
    }

    function edit_state($tab) {
        $text = ( ! empty($this->state->draft)) ? 
                $this->state->draft : 
                $this->app->taskpaper()->raw();
        $view = array('text' => $text) + $this->app->views->meta();
        $this->respond(eRespType::EDIT, $view, new Address($tab, 'edit'));
    }
    

    // *****************************************
    // ACTION ONLY requests, no new page address
    // *****************************************
    
    
    function add($task) {
        $project_num = 0;

        // Which project to add the task to?
        $address = $this->app->state()->address;
        if ($address->action == 'project') {
            $project_num = $address->value;
        } else {
            list($task, $project_num) = $this->_split_task_and_project($task);
        }
        $success = $this->app->taskpaper()->add($task, $project_num);
        if ($success) {
            $this->route();
        }
        return $success;
    }

    protected function sort_tasks($order) {
        $address = $this->app->state()->address;
        if ($address->action == 'project') {
            $project = $address->value;
        } else {
            $project = null;
        }
        $this->app->taskpaper()->reorder_tasks($order, $project);
        $this->route();
    }

    protected function sort_projects($order) {
        $this->app->taskpaper()->reorder_projects($order);
        $this->route();
    }

    protected function save($raw) {
        $this->app->taskpaper()->update(UPDATE_RAW, $raw);
        $this->app->state()->reset()->save();
        $this->route();
    }

    protected function done($item) {
        $this->app->taskpaper()->items($item)->done('swap');
        $this->route();
    }

    protected function action($item, $action) {
        $this->app->taskpaper()->items($item)->action($action);
        $this->route();
    }

    protected function remove_actions() {
        $this->app->taskpaper()->remove_actions();
        $this->route();
    }

    protected function archive_done() {
        $this->app->taskpaper()->archive_done();
        $this->route();
    }

    protected function trash_done() {
        $this->app->taskpaper()->trash_done();
        $this->route();
    }

    protected function archive($item) {
        $this->app->taskpaper()->archive($item);
        $this->route();
    }

    protected function trash($item) {
        $this->app->taskpaper()->trash($item);
        $this->route();
    }

    protected function rename($name) {
        $tab = $this->app->taskpapers->rename($name);
        $this->app->state()->update(new Address($tab))->save();
        $this->route();
    }

    protected function remove() {
        $success = $this->app->taskpapers->delete();
        if ($success) {
            // grab the first tab (default when current tab is missing)
            $tab = $this->app->taskpaper()->name();
            $this->route($tab);
        }
    }

    protected function editable($key, $text) {
        if ( ! empty($key)) {
            $this->app->taskpaper()->items($key)->raw($text);
            $this->route();
        }
    }


    // actions with no direct content change or update
    
    
    protected function purgesession() {
        session_destroy();
        $this->respond(eRespType::DONE);
    }

    protected function purgecache() {
        $this->app->cache->purge();
        $this->respond(eRespType::DONE);
    }

    protected function lang($lang) {
        \tpp\ini('language', $lang);
        $this->respond(eRespType::DONE);
    }
    
    protected function toggle_debug() {
        \tpp\toggle_debug_mode();
        $this->respond(eRespType::DONE);
    }
    
    protected function logout() {
        $this->app->user->logout();
        $this->respond(eRespType::DONE);
    }
    
    protected function toggle_insert() {
        $cur = \tpp\ini('insert_pos');
        $pos = ($cur == 'top' ? 'bottom' : 'top');
        \tpp\ini('insert_pos', $pos);
        $this->respond(eRespType::DONE);
    }


    protected function toggle_notes() {
        $cur = \tpp\ini('note_state');
        $state = ($cur == 'max' ? 'min' : 'max');
        \tpp\ini('note_state', $state);
        $this->respond(eRespType::DONE);
    }
    
    
    protected function url($url_hash) {
        $address = $this->_split_address($url_hash);
        if (empty($address->action)) {
            $address->reset();
        }
        return $this->show($address);
    }

    
    protected function tab($tab) {
        return $this->_show(new Address($tab, null, null));
    }
    
    
    /**
     * Show a specific tab state. Always routes to the new state.
     * 
     * If tab does not exist it will be created.  If the tab name is the same as the previous state then the tab state will be reset.
     *
     * @param Address $address  Full page address OR just Tab name
     * @return false    on failure only
     */
    private function _show(Address $address) {

        $tab = $address->tab;

        if ($this->app->taskpapers->exists($tab)) {
            // existing tab with no action provided => route to current tab state
            if ($address->action === null) {
                $address = $this->app->states->get($tab)->address;
            }

            // Is it the same tab: if so reset tab state
            if ($tab === $this->app->state()->address->tab) {

                log&&msg('same as current address: ', $address . ' therefore no change.');
                $this->route($address->reset());
            // Or a different one?
            } else {
                $this->app->taskpapers->set($tab);

                log&&msg('routing to:', $address);
                $this->route($address);
            }

        } else {
            
            // Or finally a new tab?
            $tab = $this->app->taskpapers->create($tab);
            if ($tab !== false) {
                
                $this->app->taskpapers->set($tab);

                // which default edit state? Set the draft text if 'edit'
                $action = tpp\config('edit_new_tab') ? 'edit' : 'all';
                $address = new Address($tab, $action, null);

                log&&msg('new tab: setting state to:', $this->app->state());
                
                $this->route($address);
            } else {

                // Or plain ol' abject failure of course...
                return false;
            }
        }
    }
    
    
    // +++++++++++++++++++++++++++++++++++++++++++ //
    
    
    /**
     * grab project number from end of a user-inputed task
     */
    private function _split_task_and_project($task) {
        global $term;
        $match = array('', '', '');
        $text = $task;
        $project = 0;

        $matched = preg_match($term['add_to_proj'], $task, $match);
        if ($matched !== false && $matched > 0) {
            $text = trim($match[1]);
            $project = (int) trim($match[2], ":/ ");
        }
        return array($text, $project);
    }
    
    
    /**
     * Converts a / separated address hash into an Address object.
     * 
     * Missing values will be replaced by a blank string
     * 
     * @param string $address_hash
     * @return Address
     */
    private function _split_address($address_hash) {
        $args = explode('/', $address_hash);
        $args = array_pad($args, 3, '');
        $address = new Address($args[0], $args[1], $args[2]);
        return $address;
    }

    
    /**
     * Map current URL parameters to a simple array of args.
     *
     * The mapping array defines the order in which arguments will be delivered to the 'action' functions.
     *
     * E.g. if mapping: array{'action', 'value', 'tab'}
     *        from url: 'event=search??&tab=work&value=roof&value2=' 
     *          func:   
     *          args: array{'search', 'roof', 'work'}
     */
    private function _map_request($request, $mapping) {
        $args = array();
        foreach ($mapping as $map => $default) {
            if (isset($request->$map)) {
                if ($request->$map !== null) {
                    $args[] = $request->$map;
                } else {
                    $args[] = $default;
                }
            }
        }
        return $args;
    }
}