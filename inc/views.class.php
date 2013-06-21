<?php
namespace tpp\view;
use tpp\model, tpp;

/**
 * The main View class;
 * Used by Dispatcher to render the various templates
 *
 * @author syanna
 */
class Views {
    private $_taskpapers;

    function __construct(model\Taskpapers $taskpapers) {
        // NOTE: a reference to a specific taskpaper is not used
        // here, as this could change during the request (e.g. show_tab)
        $this->_taskpapers = $taskpapers;
    }

    /**
     * Complete refresh of page, rebuild all content from scratch i.e. index.php
     */
    function index($address) {
        global $jslang;
        global $term;

        $view = new Template('index');
        $view->jslang = json_encode($jslang);
        $view->page_address = $address;
        $view->langs = tpp\config('lang_list');
        $view->cur_lang = tpp\ini('language');
        $view->task_prefix = $term['task_prefix'];
        $view->task_buttons = new Template('taskbuttons');

        // child views
        $view->tabs = $this->tabs();
        $view->header = new Template('header');
        $view->projects = $this->projects();
        $view->filters = $this->filters();
        $view->tags = $this->tags();
        $view->tasks = $this->all();
        return $view;
    }

    function all() {
        $active = $this->_taskpapers->active();

        $view = new TaskTemplate('tasks');
        $view->tasks = $active->items();
        $view->task_count = $active->items()->count();
        return $view;
    }

    function project($index) {
        $active = $this->_taskpapers->active();

        $view = new TaskTemplate('project');
        $view->tasks = $active->search()->by_project($index);
        $view->header = $view->tasks->title();
        $view->restricted = $active->restricted();
        return $view;
    }

    function results(model\FilteredItems $result, $header = '', $search_expr = '') {
        $header = ($header == '') ? tpp\lang('search_header') : $header;
        $search_expr = ($search_expr == '') ? $result->title() : $search_expr;

        $view = new TaskTemplate('results');
        $view->header = $header;
        $view->search_expr = $search_expr;
        $view->project_count = $result->projects()->count();
        $view->projects = $result->projects();
        $view->task_count = $result->count();
        $view->tasks = $result;
        $view->restricted = $this->_taskpapers->active()->restricted();
        $view->show_project = true;
        return $view;
    }

    function tabs() {
        $view = new Template('tabs');
        $view->tabs = $this->_taskpapers->tabs();
        $view->active = $this->_taskpapers->active()->name();
        return $view;
    }

    function projects() {
        $view = new Template('projects');
        $view->projects = $this->_taskpapers->active()->projects();
        return $view;
    }

    function tags() {
        $view = new Template('tags');
        $view->tags = $this->_taskpapers->active()->tags();
        return $view;
    }

    function filters() {
        $view = new Template('filters');
        $view->filters = tpp\lang('filter_settings');
        return $view;
    }

    function tabtools() {
        $view = new Template('tabtools');
        $view->restricted = $this->_taskpapers->active()->restricted();
        return $view;
    }


    /**
     * Fetch various views as arrays, to be sent as json via ajax
     */

    function all_json() {
        return array('tasks' => $this->all()->render()) + $this->meta_json();
    }

    function project_json($project) {
        return array('tasks' => $this->project($project)->render()) + $this->meta_json();
    }

    function results_json($result) {
        return array('tasks' => $this->results($result)->render()) + $this->meta_json();
    }

    function edit_json($text) {
        return array('text' => $text) + $this->meta_json();
    }

    function meta_json() {
        return array('tabs' => $this->tabs()->render(),
                     'tabtools' => $this->tabtools()->render(),
                     'projects' => $this->projects()->render(),
                     'tags' => $this->tags()->render(),
                     'restricted' => $this->_taskpapers->active()->restricted());
    }
    
    
    /**
     * Login views
     */
    function login_user() {
        $view = new Template('login');
        $view->form = new Template('existuser');
        return $view;
    }
    
    
    function new_user() {
        $view = new Template('login');
        $view->form = new Template('newuser');
        return $view;
    }
    
    
    function reset_password() {
        $view = new Template('login');
        $view->form = new Template('resetpassword');
        return $view;
    }
}