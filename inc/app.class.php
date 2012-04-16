<?php
require_once(APP_PATH . 'inc/file.class.php');
require_once(APP_PATH . 'inc/tabstate.class.php');
require_once(APP_PATH . 'inc/taskpaper.class.php');
require_once(APP_PATH . 'inc/view.class.php');
require_once(APP_PATH . 'inc/dispatcher.class.php');

/**
 * Models the entire Taskpaper application
 * Main entry point for all code, API
 *
 */
class App {
    public $files;
    public $taskpapers;
    public $view;
    public $tab_state;
    public $dispatcher;
    public $search;
    public $content;

    function __construct() {
        $this->files = new Files(ini('taskpaper_folder'));
        $this->tab_state = new TabState($this->files->first(), 'all');   // args: default tab and event!
        $this->taskpapers = new Taskpapers($this->user->state()->tab, $this->files);
        $this->view = new View($this->taskpapers);
        $this->dispatcher = new Dispatcher($this->taskpapers, $this->user, $this->view, $this->files);
    }
}
?>
