<?php
/**
 * English language
 * To change to this language change to 'language=en' in conf/config.ini file
 */

/**
 * These filters can be changes to suit your needs/whims!
 *
 * each filter consists of: name => (1. expression, 2. tooltip, 3. colour, 4.visibility)
 * name:       this will be displayed to the user (no spaces allowed, however _ will be changed to space on display)
 * expression: any valid search as used in search box (see help file), multiple terms are supported
 *             the expression can use either the language specific commands/intervals as above, or english (more consistent)
 *             you can even reuse other filter to create a new one, just put the = in front, e.g. '=late'
 * tooltip:    this will pop up when you hover the mouse over the filter, to explain it's purpose
 * colour:     identifies which CSS class to use (CSS class name suffix, see '.filter-' in style.css)
 *             currently valid colours are: blue, brown, cyan, gray, green, red, violet, yellow  (all soft pastel shades)
 * visible:    should this filter be added to Filter sidebar (true),
 *             or just be available from the search box, or used in other filters (false)
 */
$lang['filter_settings']    = array('next'  => array('*next | >week \\date', 'Next action plus next week', 'yellow', true),
                                     'soon'  => array('*todo >month \\date', 'Due in next month', 'green', true),
                                     'wait'  => array('*wait', 'Tasks waiting on someone/something', 'cyan', true),
                                     'maybe' => array('*maybe', 'Tasks to be done sometime, perhaps', 'violet', true),
                                     'todo'  => array('*todo', 'All incomplete tasks', 'blue', true),
                                     'done'  => array('*done', 'Completed tasks only', 'gray', true),
                                     'due'   => array('*todo =date \\gdate', 'Incomplete tasks with a date', 'green', true),
                                     'late'  => array('*todo <today \\gdate', 'Incomplete tasks with expired date', 'red', true),
                                    );

// search engine intervals and commands (English => Other Language)
$lang['interval_names']     = array('date' => 'date',
                                     'future' => 'future',
                                     'past' => 'past',
                                     'yesterday' => 'yesterday',
                                     'today' => 'today',
                                     'tomorrow' =>'tomorrow',
                                     'day' => 'day',
                                     'week' => 'week',
                                     'month' => 'month',
                                     'year' => 'year',
                                    );

// names of the various sorting "columns" (English => Other Language)
$lang['sort_names']         = array('task' => 'task',
                                     'date' => 'date',
                                     'gdate' => 'gdate',    // grouped dates
                                     'topic' => 'topic',
                                     'state' => 'state',
                                    );

// different states (todo, done should not be changed) (English => Other Language)
$lang['state_names']        = array('todo' => 'todo',
                                     'next' => 'next',
                                     'wait' => 'wait',
                                     'maybe' => 'maybe',
                                     'done' => 'done',
                                    );

// 0=todo, 1=next, etc.. done should always be last!
$lang['state_order']        = array('todo', 'next', 'wait', 'maybe', 'done');
// colours used for various states (in order of use)
// currently: none, next, wait, maybe (done has no colour)
$lang['state_colours']      = array('none', 'yellow', 'cyan', 'violet', '');

// main headers and titles
$lang['projectless']        = '[No Topic]';
$lang['tagless']            = 'No Tags';
$lang['task_header']        = 'Tasks';
$lang['tag_header']         = 'Tags';
$lang['project_header']     = 'Topics';
$lang['filter_header']      = 'Filters';
$lang['search_header']      = 'Search: ';
$lang['can_sort']           = 'Sortable';

// new tab sample content
$lang['new_tab_content']    = "New project:\n- new task @tag\n... a simple note";

// main toolbar buttons
$lang['edit_all_tip']       = 'Click here to edit the tab as plain-text';
$lang['archive_all_tip']    = 'Click to archive all completed tasks at once';
$lang['remove_action_tip']  = 'Click to remove action highlighting from all tasks at once';

// task related tips and buttons
$lang['mark_complete_tip']  = 'Click to toggle the task between done/todo';
$lang['action_toggle_tip']  = 'Click to toggle between the different actions: none » next » wait » maybe';
$lang['archive_task_tip']   = 'Click to archive this task';
$lang['delete_task_tip']    = 'Click to delete this task';
$lang['edit_in_place_tip']  = 'Double-click to edit this task in place';
$lang['project_click_tip']  = 'Click to view this topic only';
$lang['tag_click_tip']      = 'Click to filter by this tag';
$lang['search_box_tip']     = "Type in words, tags, commands, or dates to search for, or type in a new task; then press ENTER";
$lang['search_help_tip']    = "Need help searching? Click for a cheatsheet (Ctrl+click for a new page)";
$lang['startpage_tip']      = 'Back to default view';
$lang['save_changes_tip']   = 'Save your changes';
$lang['cancel_changes_tip'] = 'Cancel any changes and return to task view';
$lang['rename_tip']         = 'Click to rename this tab';
$lang['remove_tip']         = 'Click to delete this tab';
$lang['add_tab_tip']        = 'Click to add a new tab';
$lang['change_tab_tip']     = 'Click to change to this tab. Any unsaved edits will be kept';
$lang['reset_tab_tip']      = 'Click to reset this tab back to default view';
$lang['archive_tab_tip']    = 'Click to view archived tasks';
$lang['trash_tab_tip']      = 'Click to view deleted tasks';
$lang['reveal_tip']         = "Click to toggle the note";
$lang['clear_box_tip']      = 'Click to clear the search box';
$lang['sort_tip']           = '&#10; -OR- Click & drag to change order of item';

// general control labels
$lang['find_lbl']           = 'Find:';
$lang['replace_lbl']        = 'Replace:';
$lang['help_lbl']           = 'Help';
$lang['about_lbl']          = 'About';
$lang['faq_lbl']            = 'FAQ';
$lang['website_lbl']        = 'Website';
$lang['go_lbl']             = 'Go';
$lang['save_lbl']           = 'Save';
$lang['cancel_lbl']         = 'Cancel';
$lang['placeholder']        = 'Create a task -OR- Type a search [then press ENTER]';

// used before date intervals in result interface
$lang['next_lbl']           = 'in next';
$lang['prev_lbl']           = 'in previous';
$lang['before_lbl']         = 'before';
$lang['after_lbl']          = 'after';
$lang['no_date_hdr']        = 'No date';

// used by javascript side to display messages
$lang['alert_messages']     = 'Task added|Task edited|Task deleted|Task archived|All completed tasks archived|' .
                              'What is the new name for this tab?|Delete this tab?|What is the name of the new tab?|' .
                              'Make your changes and click Save or Cancel|' .
                              $lang['save_lbl'] . '|' . $lang['cancel_lbl'];
?>
