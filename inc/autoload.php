<?php

function __autoload($class) {

    // directories to seach for class files
    $dirs = array('inc', 'tpl');

    /* Any class file containing multiple classes
     * Only classes instantiated external to the file really matter,
     * classes only instantiated within the file do not need to be listed her
     */
    $class_files['taskpapers'] = array('Taskpapers',
                                       'Taskpaper',
                                       'BasicItem',
                                       'TaskItem',
                                       'ProjectItem',
                                       'InfoItem'
                                       );
    $class_files['states'] = array('States', 'State');
    $class_files['content'] = array('Content', 'ContentBuilder');
    $class_files['tabs'] = array('Tabs', 'Tab');
    $class_files['task'] = array('TaskTemplate');
    $class_files['search'] = array('IntervalFilter');

    $parts = explode('\\', $class);
    $class = array_pop($parts);

    foreach($class_files as $key => $class_file) {
        if (in_array($class, $class_file)) {
            $class = $key;
            break;
        }
    }

    foreach ($dirs as $dir) {
        $path = APP_PATH . $dir . '/' . strtolower($class) . '.class.php';
        if (file_exists($path)) {
            require_once($path);
            break;
        }
    }
}