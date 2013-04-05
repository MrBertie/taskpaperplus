<?php
namespace tpp;

/**
 * Regexes|symbols used to parse the various parts of a taskpaper.
 *
 * If you know what you are doing you can use these to adapt the style to your own preference.
 */
$term = array();

// task syntax
$term['proj_suffix']      = ':';
$term['done_prefix']      = 'X';
$term['task_prefix']      = '-';
$term['tag_prefix']       = '#';
$term['action_suffix']    = '*';
$term['note_prefix']      = '..';
$term['date_sep']         = '..';
$term['proj_sep']         = '. ';

$i8n_word                  = '[-_\p{L}\p{N}]';
$date_only                 = '\d{1,2}[-.,\/](\d{1,2}|\w{3})[-.,\/]\d{2,4}';
$action                    = '(\s[' . $term['action_suffix'] . ']{1,5})?';
$is_task                   = '((?i)' . $term['done_prefix'] . '?(?-i))' . $term['task_prefix'];

// main task parsing regexs
$title                    = '\s*((\d+):)?(.*)\s*';                                  // title text
$term['md_title']         = '/' . $title . '/';                                     // Markdown style title
$term['md_title_ul']      = '/={4,12}/';                                            // Markdown style title underline
$term['doku_title']       = '/==' . $title . '==/';                                 // Dokuwiki style

$term['project']          = '/(.+)' . $term['proj_suffix'] . '$/';                  // a topic/project
$term['task']             = '/^' . $is_task . '(.+?)/';                             // a full task
$term['split_task']       = '/^' . $is_task . '(.+?)' . $action . '(\n|$)/';        // separating the task text and action
$term['date']             = '~' . $date_only . '~';                                 // general dates
$term['tag']              = '/' . $term['tag_prefix'] . '(' . $i8n_word . '+)/';    // normal tag
$term['tag_date']         = '~' . $term['tag_prefix'] . '(' . $date_only . ')~';    // date tag
$term['action']           = '/' . $action . '(\n|$)/';                              // action on the end
$term['info']             = '/^(?!' . $term['done_prefix'] . '?' . $term['task_prefix'] . ').+(?<!' . $term['proj_suffix'] . ')$/';
$term['indent_note']      = '/^\s{2,4}(.+)/';                                       // indented style note

// basic task text formatting (used in task.tpl.php)
$term['hyperlink']        = '/\[([^|]+?)(\|(.+?))?\]/';
$term['italic']           = '`[^:]\/\/(.+?)\/\/`';
$term['bold']             = '|\*\*(.+?)\*\*|';
$term['underline']        = '|__(.+?)__|';
$term['format_chars']     = '/*_[';   // used to avoid unnecessary replacements for above

// search box syntax
$term['or_operator']      = '|';
$term['state_prefix']     = '*';
$term['date_prefix']      = '=';
$term['filter_prefix']    = '#';
$term['sort_asc_prefix']  = '/';
$term['sort_desc_prefix'] = '\\';

// search expression parser: token regexes
$term['filter_tok']       = '/[' . $term['filter_prefix'] . '](\w+)/u';
$term['sort_tok']         = '~^(\\\\|\/)(\w+)~u';
$term['word_tok']         = '/(\-)?(.+)/';
$term['date_tok']         = '~([' . $term['date_prefix'] . ']|>|<)?(' . $date_only . ')~u';
$term['range_tok']        = '~('. $date_only . ')\.\.(' . $date_only . ')~u';
$term['interval_tok']     = '~([' . $term['date_prefix'] . ']|>|<)(\d{0,2})(\w+)~u';
$term['state_tok']        = '/[' . $term['state_prefix'] . '](\w+)/u';
$term['add_to_proj']      = '`(.+?)(\/\d{1,2}$|\d{1,2}:)$`';  // the add to project syntax in search box (/1 or 1:)