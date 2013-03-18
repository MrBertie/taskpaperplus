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

        log&&msg('Setting up the app API');

        $this->user = new user\User(APP_PATH . config('user_file'));
        $this->files = new storage\Files(ini('taskpaper_folder'),
                                         config('data_dir'),
                                         config('default_active')
                                        );
        $this->cache = new storage\Cache($this->files);
        $this->parser = new storage\Parser();
        $this->states = new user\States($this->files);

        log&&msg('building taskpapers');

        $this->taskpapers = new model\Taskpapers($this->states->active()->tab,
                                                 $this->files,
                                                 $this->cache
                                                );
        $this->views = new view\Views($this->taskpapers);
        $this->dispatcher = new control\Dispatcher($this);

        log&&msg('Finished setting up the app API');
    }
}
?>