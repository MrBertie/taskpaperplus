<?php
namespace tpp\view;
use tpp\model, tpp\user, tpp;

/**
 * The main View class.  Used by Dispatcher to render the various templates.
 * 
 * Renders the currently active Taskpaper.
 *
 * @author syanna
 */
class Views {
    private $_taskpapers;
    private $_user;
    

    function __construct(model\Taskpapers $taskpapers, user\User $user) {
        // NOTE: a reference to a specific taskpaper is not used here, 
        // as this could change during the request (e.g. show_tab)
        $this->_taskpapers = $taskpapers;
        $this->_user       = $user;
    }

    
    /**
     * Complete refresh of page, rebuild all content from scratch i.e. index.php
     */
    function index($address) {
        global $jslang;
        global $term;

        $view = new Template('index');
        
        // page data (hidden)
        $view->debug_mode   = DEBUG_MODE;
        $view->jslang       = json_encode($jslang);
        $view->page_address = $address;
        $view->insert_pos   = \tpp\ini('insert_pos');
        $view->note_state   = \tpp\ini('note_state');
        $view->task_prefix  = $term['task_prefix'];

        // child views
        $view->task_buttons = new Template('taskbuttons');
        $view->tabs         = $this->tabs();
        $view->header       = new Template('header');
        $view->projects     = $this->projects();
        $view->filters      = $this->filters();
        $view->tags         = $this->tags();
        $view->tasks        = $this->all();
        $view->footer       = $this->footer();
        return $view;
    }

    
    function all() {
        $active = $this->_taskpapers->get();

        $view             = new TaskTemplate('tasks');
        $view->tasks      = $active->items();
        $view->task_count = $view->tasks->count();
        $view->restricted = $active->restricted();
        return $view;
    }

    
    function project($index) {
        $active = $this->_taskpapers->get();

        $view = new TaskTemplate('project');
        $result           = $active->search()->by_project($index);
        $view->tasks      = $result;
        $view->task_count = $result->count();
        $view->projects   = $view->tasks->projects();
        $view->header     = $view->tasks->title();
        $view->restricted = $active->restricted();
        return $view;
    }

    
    function results(model\FilteredItems $result, $header = '', $search_expr = '') {
        $header = ($header == '') ? tpp\lang('search_header') : $header;
        $search_expr = ($search_expr == '') ? $result->title() : $search_expr;

        $view = new TaskTemplate('results');
        $view->header        = $header;
        $view->search_expr   = $search_expr;
        $view->project_count = $result->projects()->count();
        $view->projects      = $result->projects();
        $view->task_count    = $result->count();
        $view->tasks         = $result;
        $view->restricted    = $this->_taskpapers->get()->restricted();
        $view->show_project  = true;
        return $view;
    }
    
    
    function search($expression) {
        $result = $this->_taskpapers->get()->search()->by_expression($expression);
        return $this->results($result);
    }
    
    
    function filter($filter) {
        $result = $this->_taskpapers->get()->search()->by_named_filter($filter);
        return $this->results($result);
    }

    
    function tag($tag) {
        $result = $this->_taskpapers->get()->search()->by_tag($tag);
        return $this->results($result);
    }

    
    function tabs() {
        $view = new Template('tabs');
        $view->tabs   = $this->_taskpapers->tabs();
        $view->active = $this->_taskpapers->get()->name();
        return $view;
    }

    
    function projects() {
        $view = new Template('projects');
        $view->projects = $this->_taskpapers->get()->projects();
        return $view;
    }

    
    function tags() {
        $view = new Template('tags');
        $view->tags = $this->_taskpapers->get()->tags();
        return $view;
    }

    
    function filters() {
        $view = new Template('filters');
        $view->filters = tpp\lang('filter_settings');
        return $view;
    }

    
    function tabtools() {
        $view = new Template('tabtools');
        $view->restricted = $this->_taskpapers->get()->restricted();
        return $view;
    }
    
    
    function footer() {
        $view = new Template('footer');
        $view->langs        = tpp\config('lang_list');
        $view->cur_lang     = tpp\ini('language');
        $view->logged_in_as = $this->_user->logged_in_as();
        return $view;
    }

    
    function meta() {
        return $this->tabs()->json() +
               $this->tabtools()->json() +
               $this->projects()->json() +
               $this->tags()->json() +
               array('restricted' => $this->_taskpapers->get()->restricted());
    }
}