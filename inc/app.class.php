<?php
namespace tpp;
use tpp\user, tpp\storage, tpp\control, tpp\view, tpp\model;


/**
 * Models the entire Taskpaper application
 * Main entry point for all code, API
 *
 */
class App {

    public $dispatcher;
    public $files;
    public $parser;
    public $states;
    public $taskpapers;
    public $user;
    public $view;

    function __construct() {

        $this->user       = new user\User(APP_PATH . config('user_file'));
        $this->files      = new storage\Files(DATA_DIR, DELETED_DIR, config('default_active')     
        );     
        $this->cache      = new storage\Cache(CACHE_DIR, $this->files);
        $this->parser     = new storage\Parser();
        $this->states     = new user\States($this->files);
        
        $tab              = $this->state()->address->tab;
        $this->taskpapers = new model\Taskpapers($tab, $this->files, $this->cache
        );
        $this->views      = new view\Views($this->taskpapers, $this->user);
        $this->dispatcher = new control\Dispatcher($this);

        \log&&msg('Finished setting up the app API');
    }
    
    
    /**
     * Always returns current/active taskpaper (convenience func).
     * @return type
     */
    function taskpaper() {
        return $this->taskpapers->get();
    }
    
    /**
     * Always returns current/active tab state (convenience func).
     * @return type
     */
    function state() {
        return $this->states->get();
    }
}