<?php
/*
 * default application config files
 *
 */
$config = array();

$config['version_number']   = '0.9.6.3 beta | 18-Feb-2012';
$config['debug_file']       = 'logs/debug.txt';        // relative to App Base Path
$config['log_file']         = 'logs/log.txt';
$config['data_folder']      = 'data';
$config['deleted_path']     = '_deleted/';
$config['cache_path']       = '_cache/';
$config['hide_tips']        = false;    // hide all pop up tool tips
$config['edit_new_tab']     = true;     // open new tabs in 'edit' state

$config['title']            = 'Taskpaper+';
$config['default_active']   = 'tasks';
$config['date_format']      = 'j-M-Y';

// task syntax
$config['proj_suffix']      = ':';
$config['done_prefix']      = 'X';
$config['task_prefix']      = '-';
$config['tag_prefix']       = '@';
$config['action_suffix']    = '*';
$config['note_prefix']      = '...';    // change note_rgx / block_note_rgx as well!
$config['date_sep']         = '..';

// basic task text formatting
$config['hyperlink']        = '/\[([^|]+?)(\|(.+?))?\]/';
$config['italic']           = '`[^:]\/\/(.+?)\/\/`';
$config['bold']             = '|\*\*(.+?)\*\*|';
$config['underline']        = '|__(.+?)__|';
$config['format_chars']     = '/*_[';   // used to avoid unnecessary replacements for above

// search box syntax
$config['or_operator']      = '|';
$config['state_prefix']     = '*';
$config['date_prefix']      = '=';
$config['filter_prefix']    = '#';
$config['sort_asc_prefix']  = '/';
$config['sort_desc_prefix'] = '\\';

// header for grouped dates
$config['group_date']       = 'M-Y';

$i8n_word                   = '[-_\p{L}\p{N}]';
// regexes used to identify the various parts of a taskpaper;
// this allows the user to adapt the style to his own preference
$date_only                  = '\d{1,2}[-.,\/](\d{1,2}|\w{3})[-.,\/]\d{2,4}';
$config['date_rgx']         = '~' . $date_only . '~';

$config['project_rgx']      = '/(.+)' . $config['proj_suffix'] . '$/';
$config['note_rgx']         = '/^\.\.\.(.+)/';
$config['block_note_rgx']   = '/^\.\.\.\n((.*\n)*?)\.\.\.$/';
$config['task_rgx']         = '/^' . $config['done_prefix'] . '*' . $config['task_prefix'] . '.+/m';
$config['tag_rgx']          = '/' . $config['tag_prefix'] . '(' . $i8n_word . '+)/';    // normal tag
$config['date_tag_rgx']     = '~' . $config['tag_prefix'] . '(' . $date_only . ')~';   // date tag
$config['action_rgx']       = '/\s([' . $config['action_suffix'] . ']{1,5})(\n|$)/';

// search expression parser: token regexes
$config['filter_tok_rgx']   = '/[' . $config['filter_prefix'] . '](\w+)/u';
$config['sort_tok_rgx']     = '~^(\\\\|\/)(\w+)~u';
$config['word_tok_rgx']     = '/(\-)?(.+)/';
$config['date_tok_rgx']     = '~([' . $config['date_prefix'] . ']|>|<)?(' . $date_only . ')~u';
$config['range_tok_rgx']    = '~('. $date_only . ')\.\.(' . $date_only . ')~u';
$config['interval_tok_rgx'] = '~([' . $config['date_prefix'] . ']|>|<)(\d{0,2})(\w+)~u';
$config['state_tok_rgx']    = '/[' . $config['state_prefix'] . '](\w+)/u';
$config['in_proj_rgx']      = '`((?<=\/)\d{1,2}$|\d{1,2}(?=:$))`';  // the add to project syntax in search box (/1 or 1:)
?>
