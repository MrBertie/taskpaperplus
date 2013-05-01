<?php
namespace tpp\model;
use tpp;

/**
 * Handles all search functions for current taskpaper, or
 * a specific one (for API use)
 */
class Search {

    private $_content;
    private $_parent;
    private $_group_dates = false;

    function __construct(Taskpaper $parent, Content $content) {
        $this->_content = $content;
        $this->_parent = $parent;
    }

    function by_expression($expression) {
        $filter = new ExpressionFilter($this->_content);
        $tasks = $filter->filter($this->_content->raw_items, $expression);
        $title = $filter->parsed_term();
        return $this->_filter_and_sort_tasks($tasks, $title, $filter->sort_filters());
    }

    function by_project($index) {
        // returns a specific project
        $name = $this->_content->project_by_index($index)->text;
        // get a list of tasks (key only) in this project
        $task_keys = array_keys($this->_content->task_project, $index);
        // now finally extract the right task lines
        $task_keys = array_flip($task_keys);
        $tasks = array_intersect_key($this->_content->raw_items, $task_keys);
        return $this->_filter_and_sort_tasks($tasks, $name, array(), false);    // do not show projects
    }

    function by_tag($tag) {
        global $term;

        // return tasks by specific tag (could be a date tag also)
        $has_date = preg_match($term['date'], $tag, $matches);
        if ($has_date == 1) {
            $date = $term['date_prefix'] . $matches[0];
            $date_filter = new DateFilter($this->_content);
            $tasks = $date_filter->filter($this->_content->raw_items, $date);
            return $this->_filter_and_sort_tasks($tasks, $date);
        } else {
            $tag = $term['tag_prefix'] . $tag;    //search is for tags ONLY (not matching words)
            $word_filter = new WordFilter($this->_content);
            $tasks = $word_filter->filter($this->_content->raw_items, $tag);
            return $this->_filter_and_sort_tasks($tasks, $tag);
        }
    }

    // filter name WITHOUT the prefix (=)
    function by_named_filter($name) {
        global $term;

        $named_filter = new NamedFilter($this->_content);
        $tasks = $named_filter->filter($this->_content->raw_items, $term['filter_prefix'] . $name);
        $name = $named_filter->parsed_term();
        return $this->_filter_and_sort_tasks($tasks, $name, $named_filter->sort_filters());
    }


    // ********
    private function _filter_and_sort_tasks($tasks, $title = '', array $sort_filters = array()) {
        global $term;

        list($tasks, $projects) = $this->_separate_projects($tasks);
        if (!empty($sort_filters)) {
            list($tasks, $dates) = $this->_sort_results($tasks, $sort_filters);
        }
        $task_hits = count($tasks);
        $groups = array();
        if ($this->_group_dates) {
            $prev_month = '';
            foreach ($tasks as $key => $task) {
                $date = current($dates);
                $month = ($date != 0) ? date($term['group_date'], $date) : tpp\lang('no_date_hdr');
                if ($prev_month != $month) {
                    $groups[$key] = $month . $term['proj_suffix'];
                    $prev_month = $month;
                }
                next($dates);
            }
        }
        $project_hits = count($projects);
        return new FilteredItems($tasks, $task_hits, $projects, $project_hits, $title, $groups);
    }


    private function _separate_projects($items) {
        $tasks = array_diff_key($items, $this->_content->project_index);
        $tasks = ($tasks === null) ? array() : $tasks;
        $tasks = array_filter($tasks);
        $projects = array_intersect_key($items, $this->_content->project_index);
        $projects = ($projects === null) ? array() : $projects;
        return array($tasks, $projects);
    }

    private function _sort_results(array $tasks, array $sort_filters) {
        $sort_args = array();
        $dates = array();

        foreach ($sort_filters as $sort_filter) {
            $dir = $sort_filter->dir;
            $sort_by = $sort_filter->sort_by;
            switch ($sort_by) {
                case 'date':
                case 'gdate':
                    $dates = $this->_content->task_date;
                    $dates = array_intersect_key($dates, $tasks);
                    $sort_args[] = array($dates, $dir, SORT_NUMERIC);
                    $date_key = key($sort_args);
                    break;
                case 'topic':
                    $projects = $this->_content->task_project;
                    $projects = array_intersect_key($projects, $tasks);
                    $sort_args[] = array($projects, $dir, SORT_NUMERIC);
                    break;
                case 'task':
                    $sort_args[] = array($tasks, $dir, SORT_STRING);
                    break;
                case 'state':
                    $states = $this->_content->task_state;
                    $states = array_intersect_key($states, $tasks);
                    $sort_args[] = array($states, $dir, SORT_NUMERIC);
                    break;
            }
        }
        $this->_multisort($tasks, $sort_args);

        // grouping by month if last sort was by gdate
        if ($sort_by == 'gdate') {
            $this->_group_dates = true;
            // get the sorted date array also
            $dates = $sort_args[$date_key][0];
        }
        return array($tasks, $dates);
    }


    // sort by each sorting array, in order
    private function _multisort(&$tasks, &$sort_args) {
        if ( ! empty($sort_args)) {
            $args = array();
            foreach ($sort_args as &$sort_arg) {
                $args[] = &$sort_arg[0];
                $args[] = &$sort_arg[1];
                $args[] = &$sort_arg[2];
            }
            $args[] = &$tasks;
            call_user_func_array('array_multisort', $args);
        }
    }
}

/**
 * Base token filter class: models a search expression token
 *
 */
class TokenFilter {
    protected $_content;
    protected $_token;
    protected $_parsed_term = '';
    protected $_matched_tokens = array();
    protected $_matched = false;
    protected $_sort_filters = array(); // list of sort filters in this filter expression
    public $type = '';

    function __construct($content) {
        $this->_content = $content;
        $this->type= 'token';
    }
    // returns true if this token matches (matching order is important!)
    function match($token) {
        // TODO: check if assigning _token causes problems elsewhere
        $this->_token = $token;
        $this->_matched = true;
        return true;
    }
    function matched() {
        return $this->_matched;
    }
    /**
     * return the tasks filtered by this token only
     * remember to fill in _hits if necessary!
     * You can also pass the token directly in here, if you know it matches
     * @param array $tasks
     */
    function filter(array $tasks, $token = '') {
    }
    // return the full search term as understood by parser (handy for partial  matches)
    function parsed_term() {
        return $this->_parsed_term;
    }
    // returns the different parts of the token (prefix, word, operator, etc...)
    function matched_tokens() {
        return $this->_matched_tokens;
    }
    function sort_filters() {
        return $this->_sort_filters;
    }
    function has_sort() {
        return !empty($this->_sort_filters);
    }
    protected function _find_match($token = '', $pattern = '') {
        $matched = false;
        if (empty($token) && !empty($this->_matched_tokens)) {
            $matched = true;
        } elseif ($token != '') {
            $match_count = preg_match($pattern, $token, $matches);
            if ($match_count > 0) {
                $this->_matched_tokens = $matches;
                $matched = true;
            }
        }
        return $matched;
    }

    protected function _match_name($name, Array $items, $key_only = false) {
        // preg_grep allows for partial matches also, we use only first match
        // first try to match in the values (localised words)
        $matches = (!$key_only) ? preg_grep('~^' . $name . '~u', $items) : array();
        if (!empty($matches)) {
            $key = key($matches);
            return array(key($matches), current($matches));
        // if no match, then try the keys (english words); this feature is often used by other functions
        } else {
            $matches = preg_grep('~^' . $name . '~u', array_keys($items));
            if (!empty($matches)) {
                $key = current($matches);
                $name = (!$key_only) ? $items[$key] : $key;
                return array($key, $name);
            }
        }
        return array(false, false);
    }
}



/**
 * Parses any complete search expression, calls necessary Filter classes
 * (some classes, e.g. NamedFilter will recursively call ExpressionParser as needed)
 */
class ExpressionFilter extends TokenFilter {

    function filter(array $tasks, $expression = '') {
        $this->type = 'expression';
        return $this->_filter($tasks, $expression);
    }


    private function _tokenise($expression) {
        // quoted phrases are replaced by a marker token `n` and later replaced in token array
        $phrases = preg_match_all('~\"(.+?)\"~', $expression, $matches, PREG_PATTERN_ORDER);
        if($phrases !== false) {
            for($i = 0; $i < $phrases; $i++) {
                $expression = preg_replace('~' . $matches[0][$i] . '~', '`' . $i . '`', $expression);
            }
        }
        $tokens = explode(' ' , $expression);
        // replace and phrases using `n` marker token
        $tokens = preg_replace('~`(\d)`~e', '$matches[1]["$1"]', $tokens);
        return $tokens;
    }

    private function _filter($tasks, $expression) {
        // build a queue of expression tokens
        $tokens = $this->_tokenise($expression);
        $token_filters = array();
        $parsed_terms = '';
        $sort_terms = '';
        $filter_classes = array('OrFilter', 'RangeFilter', 'DateFilter', 'IntervalFilter',
                                 'StateFilter', 'SortFilter', 'NamedFilter', 'WordFilter');
        $ns = '\\tpp\\model\\';
        foreach ($tokens as $token) {
            // try to match token type (order matters)
            foreach ($filter_classes as $filter_class) {
                $class = $ns . $filter_class;
                $test_filter = new $class($this->_content);
                if ($test_filter->match($token) === true) {
                    if ($filter_class == 'SortFilter') {
                        $this->_sort_filters[] = $test_filter;
                        $sort_terms .= $test_filter->parsed_term() . ' ';
                        break;
                    } else {
                        $parsed_terms .= $test_filter->parsed_term() . ' ';
                        $token_filters[] = $test_filter;
                        break;
                    }
                }
            }
        }
        $or = false;
        $filt_tasks = $tasks;
        foreach ($token_filters as $token_filter) {
            if ($token_filter->type == 'or') {
                $or = true;
                continue;
            }
            if ($or) {
                $or_tasks = $token_filter->filter($tasks);
                $filt_tasks = array_merge($filt_tasks, $or_tasks);  // matching keys will overwrite each other!
                $or = false;
            } else {
                $filt_tasks = $token_filter->filter($filt_tasks);
            }
            if ($token_filter->has_sort()) {
                array_merge($this->_sort_filters, $token_filter->sort_filters());
            }
        }
        $this->_parsed_term = trim($parsed_terms . ' ' . $sort_terms) ;
        return $filt_tasks;
    }
}



class DateTokenFilter extends TokenFilter {

    function filter_by_date(array $tasks, $start_date = 0, $end_date = 0, $operator = '') {
        // first make some sense out of the start and end dates provided
        if (!is_int($start_date)) {
            $start_date = strtotime(strtolower(trim($start_date)));
        }
        if (!is_int($end_date)) {
            $end_date = strtotime(strtolower(trim($end_date)));
        }
        if ($start_date !== false) {
            if ($end_date === false || empty($end_date)) {
                switch (trim($operator)) {
                case '>':
                    $end_date = 0;
                    break;
                case '<':
                    $end_date = $start_date;
                    $start_date = 0;
                    break;
                default:
                    $end_date = $start_date;
                }
            }
            $due_tasks = array();
            $dates = array_filter($this->_content->task_date);  // all dated tasks (key only)
            if (!empty($dates)) {
                if (!($start_date == 0 && $end_date == 0)) {
                    foreach ($dates as $key => $date) {
                        $before_end = $start_date == 0 && $date <= $end_date;
                        $after_start = $end_date == 0 && $date >= $start_date;
                        $between = ($date >= $start_date) && ($date <= $end_date);
                        if (!($before_end || $after_start || $between)) {
                            unset($dates[$key]);
                        }
                    }
                }
            }
            $due_tasks = array_intersect_key($tasks, $dates);
            return $due_tasks;
        }
        return array();
    }
}


class WordFilter extends TokenFilter {
    private $_word = '';
    private $_exclude = '';

    function match($token) {
        global $term;

        $this->type = 'word';
        $this->_matched = false;
        $token = ($token == '') ? $this->_token : $token;
        if ($this->_find_match($token, $term['word_tok']) === true) {
            $parsed_term = $this->_matched_tokens[0];
            $this->_parsed_term = (strpos($parsed_term, ' ') > 0) ? '"' . $parsed_term . '"' : $parsed_term;
            $first = $this->_matched_tokens[1];
            if (empty($first) || $first == '-') {
                $this->_exclude = $first;
                $this->_word = $this->_matched_tokens[2];
            } else {
                $this->_word = $first;
            }
            $this->_matched = true;
        }
        return $this->_matched;
    }

    function filter(array $tasks, $token = '', $wildcard = false) {
        if (!empty($token)) {
            $this->match($token);
        }
        if ($this->matched() === true) {
            $wildcard = ($wildcard === true) ? '.' : '';
            $regex = "`" . $wildcard . $this->_word . $wildcard . "`i";
            if ($this->_exclude == '-') {
                $tasks = preg_grep($regex, $tasks, PREG_GREP_INVERT);
            } else {
                $tasks = preg_grep($regex, $tasks);
            }
        }
        return $tasks;
    }
}

/**
 * Named filter class: any named filter from lang.php file
 */
class NamedFilter extends TokenFilter {
    private $_index;
    private $_expression;

    function match($token) {
        global $term;

        $this->type = 'named';
        $this->_matched = false;
        $token = ($token == '') ? $this->_token : $token;
        if ($this->_find_match($token, $term['filter_tok']) === true) {
            $name = $this->_matched_tokens[1];
            list($key, $name) = $this->_match_name($name, tpp\lang('filter_settings'), true);
            if ($key !== false) {
                $this->_index = $key;
                $this->_parsed_term = $term['filter_prefix'] . tpp\no_underscores($name);
                $filter_settings = tpp\lang('filter_settings');
                $this->_expression = $filter_settings[$key][0];
                $this->_matched = true;
            }
        }
        return $this->_matched;
    }

    function filter(array $tasks, $token = '') {
        if (!empty($token)) {
            $this->match($token);
        }
        if ($this->matched() === true) {
            $expr_filter = new ExpressionFilter($this->_content);
            $tasks = $expr_filter->filter($tasks, $this->_expression);
            $this->_sort_filters = $expr_filter->sort_filters();
        }
        return $tasks;
    }
}
/**
 * State filter class: by done, todo, next, wait, maybe, etc...
 */
class StateFilter extends TokenFilter {

    function match($token) {
        global $term;

        $this->type = 'state';
        $this->_matched = false;
        $token = ($token == '') ? $this->_token : $token;
        if ($this->_find_match($token, $term['state_tok']) === true) {
            $name = $this->_matched_tokens[1];
            list($key, $name) = $this->_match_name($name, tpp\lang('state_names'));
            if ($key !== false) {
                // convert to a numeric index--for sorting purposes!
                $this->_index = array_search($key, tpp\lang('state_order'));
                $this->_parsed_term = $term['state_prefix'] . tpp\no_underscores($name);
                $this->_matched = true;
            }
        }
        return $this->_matched;
    }

    function filter(array $tasks, $token = '') {
        if ( ! empty($token)) {
            $this->match($token);
        }
        if ($this->matched() === true) {
            $idx = $this->_index;
            $states = $this->_content->task_state;
            // here we filter purely by state index number (0-4 currently)
            if ($idx == 1) {
                $states = array_filter($states, function($state) {
                    return $state > 0;
                });
            } else {
                $states = array_filter($states, function($state) use($idx) {
                    return $state == $idx;
                });
            }
            $tasks = array_intersect_key($tasks, $states);
        }
        return $tasks;
    }
}

class SortFilter extends TokenFilter {
    public $sort_by;
    public $dir;

    function match($token) {
        global $term;

        $this->type = 'sort';
        $this->_matched = false;
        $token = ($token == '') ? $this->_token : $token;
        if ($this->_find_match($token, $term['sort_tok']) === true) {
            $dir = $this->_matched_tokens[1];
            $sort_by = $this->_matched_tokens[2];
            list($key, $sort_by) = $this->_match_name($sort_by, tpp\lang('sort_names'));
            if ($key !== false) {
                $this->sort_by = $key;
                if ($dir == $term['sort_desc_prefix']) {
                    $this->dir = SORT_DESC;
                    $dir = '▲';
                } else {
                    $this->dir = SORT_ASC;
                    $dir = '▼';
                }

                $this->_parsed_term = '(' . $sort_by . $dir . ')';
                $this->_matched = true;
            }
        }
        return $this->_matched;
    }
}

// 'and' is implicit and assumed by default
class OrFilter extends TokenFilter {

    function match($token) {
        global $term;

        $this->type = 'or';
        $this->_matched = false;
        $token = ($token == '') ? $this->_token : $token;
        if ($token == $term['or_operator'] || $token == 'OR') {
            $this->_parsed_term = '|';
            $this->_matched = true;
        }
        return $this->_matched;
    }
}

class DateFilter extends DateTokenFilter {
    private $_operator = '';
    private $_date = '';

    function match($token) {
        global $term;

        $this->type = 'date';
        $this->_matched = false;
        $token = ($token == '') ? $this->_token :$token;
        if (parent::_find_match($token, $term['date_tok']) === true) {
            $this->_operator = $this->_matched_tokens[1];
            $this->_date = $this->_matched_tokens[2];
            $this->_parsed_term = $this->_matched_tokens[0];
            $this->_matched = true;
        }
        return $this->_matched;
    }
    function filter(array $tasks, $token = '') {
        if (!empty($token)) {
            $this->match($token);
        }
        if ($this->matched() === true) {
            return $this->filter_by_date($tasks, $this->_date, 0, $this->_operator);
        } else {
            return $tasks;
        }
    }
}

class RangeFilter extends DateTokenFilter {
    private $_start_date = 0;
    private $_end_date = 0;

    function match($token) {
        global $term;

        $this->type = 'range';
        $this->_matched = false;
        $token = ($token == '') ? $this->_token :$token;
        if ($this->_find_match($token, $term['range_tok']) === true) {
            $this->_start_date = $this->_matched_tokens[1];
            $this->_end_date = $this->_matched_tokens[3];
            $this->_parsed_term = $this->_matched_tokens[0];
            $this->_matched = true;
        }
        return $this->_matched;
    }
    function filter(array $tasks, $token = '') {
        if (!empty($token)) {
            $this->match($token);
        }
        if ($this->matched() === true) {
            return $this->filter_by_date($tasks, $this->_start_date, $this->_end_date, '');
        } else {
            return $tasks;
        }
    }
}
/**
 * Converts date names into real dates
 * e.g < month => during last month             > today => from today onwards (=future)
 *     = month => during coming month           = today => today only
 *     > month => (as above)                    < today => up to (and including) today (=past)
 */
class IntervalFilter extends DateTokenFilter {
    private $_interval = '';
    private $_count = 0;
    private $_index = false;
    private $_op = '';  // operator: = > <

    function match($token) {
        global $term;

        $this->type = 'interval';
        $this->_matched = false;
        $token = ($token == '') ? $this->_token : $token;
        if ($this->_find_match($token, $term['interval_tok']) === true) {
            $interval = $this->_matched_tokens[3];
            list($key, $interval) = $this->_match_name($interval, \tpp\lang('interval_names'));
            if ($key !== false) {
                $op = $this->_matched_tokens[1];
                $count = $this->_matched_tokens[2];
                if (strpos('day week month year', $key) !== false) {
                    $interval .= ($count > 1) ?  's' : '';
                    $next = ($op == '<') ? tpp\lang('prev_lbl') : \tpp\lang('next_lbl');
                    $next .=  ' ';
                    $count = ($count > 1) ? $count : 1;
                    $number = ($count > 1) ?  ' ' . $count . ' ' : '';
                } elseif($key == 'today') {
                    $next = ($op == '<') ? \tpp\lang('before_lbl') : \tpp\lang('after_lbl');
                    $next .=  ' ';
                    $count = 0;
                    $number = '';
                } else {
                    $next = '';
                    $count = 0;
                    $number = '';
                }
                $this->_op = $op;
                $this->_parsed_term = $op . $next . $number . $interval;
                $this->_index = $key;
                $this->_count = $count;
                $this->_interval = $interval;
                $this->_matched = true;
            }
        }
        return $this->_matched;
    }
    function filter(array $tasks, $token = '') {
        if (!empty($token)) {
            $this->match($token);
        }
        if ($this->matched() === true) {
            list($start_date, $end_date, $eq) = $this->_convert_to_date($this->_index, $this->_count, $this->_op);
            return $this->filter_by_date($tasks, $start_date, $end_date, $eq);
        } else {
            return $tasks;
        }
    }
    // must include the filter prefix '='
    function interval_as_date($interval) {
        if ($this->match($interval) === true) {
            return $this->_convert_to_date($this->_index, $this->_count);
        } else {
            return false;
        }
    }
    private function _convert_to_date($index, $count = 1, $eq = '=') {
        // replace with correct date
        $count = ($eq == '<') ? -$count : $count;
        $start_date = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $end_date = 0;
        switch ($index) {
            case 'day':
                $end_date = mktime(0, 0, 0, date("m"), date("d") + $count, date("Y"));
                break;
            case 'week':
                $end_date = mktime(0, 0, 0, date("m"), date("d") + (7 * $count), date("Y"));
                break;
            case 'month':
                $end_date = mktime(0, 0, 0, date("m") + $count, date("d") - 1, date("Y"));
                break;
            case 'year':
                $end_date = mktime(0, 0, 0, date("m"), date("d"), date("Y") + $count);
                break;
            case 'today':
                switch ($eq) {
                case '=':   // today only
                    $end_date = $start_date;
                    break;
                case '<':   // same as past
                case '>':   //same as future
                    // once again use defaults
                    break;
                }
                break;
            case 'tomorrow':
                $end_date = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
                $eq = '=';
                break;
            case 'yesterday':
                $end_date = $start_date;
                $start_date = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
                $eq = '=';
            case 'date': // all dated items (eq is ignored here)
                $start_date = 0;    // => any start date
                $eq = '=';
                break;
            case 'future':
                // just use defaults above
                $eq = '>';
                break;
            case 'past':
                $eq = '<';
                break;
            default:
                return false;
        }

        // note: date class will swap start and end dates based on eq
        return array($start_date, $end_date, $eq);
    }
}