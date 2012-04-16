<?php
require_once(APP_PATH . 'inc/template.class.php');
require_once(APP_PATH . 'tpl/task.tpl.php');

/**
 * The main View class;
 * Used by Dispatcher to render the various templates
 *
 * @author syanna
 */
class View {
    private $_taskpapers;

    function __construct(Taskpapers $taskpapers) {
        $this->_taskpapers = $taskpapers;
    }
    /**
     * Complete refresh of page, rebuild all content from scratch i.e. index.php
     */
    function index($address) {
        $main_view = new Template('index');
        $main_view->set('title', config('title'))
                  ->set('alert_messages', lang('alert_messages'))
                  ->set('page_address', $address)
                  ->set('tab_view', $this->tabs())
                  ->set('project_header', lang('project_header'))
                  ->set('project_view', $this->projects())
                  ->set('filter_header', lang('filter_header'))
                  ->set('filters', lang('filter_settings'))
                  ->set('tag_header', lang('tag_header'))
                  ->set('tag_view', $this->tags())
                  ->set('task_view', $this->all())
                  ->set('langs', config('lang_list'))
                  ->set('cur_lang', ini('language'));
        return $main_view;
    }
    function all() {
        $task_view = new Template('tasks');
        $active = $this->_taskpapers->active();
        $task_view->set('tasks', $active->tasks())
                  ->set('task_header', lang('task_header'))
                  ->set('restricted', $active->restricted())
                  ->set('projectless', lang('projectless'))
                  ->set('task_count', $active->tasks()->task_count());
        return $task_view;
    }
    function project($project) {
        $tasks = $this->_taskpapers->active()->search()->by_project($project);
        $projecttask_view = new Template('project');
        $projecttask_view->set('project_header', $tasks->title())
                          ->set('project_tasks', $tasks)
                          ->set('restricted', $this->_taskpapers->active()->restricted());
        return $projecttask_view;
    }
    function results(FilteredItems $result, $result_header = '', $search_expr = '') {
        $proj_count = $result->project_count();
        $task_count = $result->task_count();
        $result_view = new Template('results');
        $result_header = ($result_header == '') ? lang('search_header') : $result_header;
        $search_expr = ($search_expr == '') ? $result->title() : $search_expression;
        $result_view->set('result_header', $result_header)
                    ->set('search_expression', $search_expr)
                    ->set('project_header', lang('project_header'))
                    ->set('project_count', $proj_count)
                    ->set('project_results', $result->projects())
                    ->set('task_header', lang('task_header'))
                    ->set('task_count', $task_count)
                    ->set('task_results', $result)
                    ->set('restricted', $this->_taskpapers->active()->restricted())
                    ->set('show_project', true);
        return $result_view;
    }
    function projects() {
        $project_view = new Template('projects');
        $project_view->set('projects', $this->_taskpapers->active()->projects());
        return $project_view;
    }
    function tags() {
        $tag_view = new Template('tags');
        $tag_view->set('tags', $this->_taskpapers->active()->tags());
        return $tag_view;
    }
    function tabs() {
        $filetab_view = new Template('tabs');
        $filetab_view->set('file_names', $this->_taskpapers->items())
                     ->set('active', $this->_taskpapers->active()->name());
        return $filetab_view;
    }
}
?>
