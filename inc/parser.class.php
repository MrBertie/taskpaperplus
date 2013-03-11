<?php
namespace tpp\storage;
use tpp\model, tpp;

/**
 * Parses the Taskaper plain-text file into Projects, Tasks, Notes, and Info.
 *
 * All data is returned in a Content data structure, used by the Cache/Taskpaper classes.
 *
 * @author Symon Bent
 */
class Parser {

    private $_lines;     // Line object: simple line tokeniser for the raw taskpaper;
    private $_content;
    private $_index;


    function __construct() {
        global $term;
        $this->term = $term;
    }


    function parse($raw) {
        $this->_lines = new Lines($raw);
        $this->_content = new model\Content;
        $this->_filter = new model\IntervalFilter($this->_content);
        $this->_index = 0;

        // Abstract Syntax Tree
        $ast = $this->term_start();
        return $ast;
    }


    /**
     * Parse the main taskpaper page
     * This includes the display Title and Number
     *
     * @global type $token
     * @param Line $this->lines   the line tokeniser instance
     * @return array    AST of the taskpaper
     */
    private function term_start() {

        list($text, $index, $note, $raw) = $this->match_any_one(array('term_title_doku', 'term_title_md', 'term_title_none'));

        $page = (object) array('type' => 'page', 'text' => $text, 'index' => $index, 'note' => $note, 'raw' => $raw);

        // <start> ::= <project>* | empty* [silent, ignored]
        $page->children = $this->match_any(array('term_project', 'term_empty'), array());
        return $page;
    }


    private function term_title_doku() {
        $line = $this->_lines->cur();
        if (preg_match($this->term['doku_title'], $line, $match) > 0) {
            $this->_lines->move();
            $text = trim(tpp\isset_or($match[3], ''));
            $note = $this->term_note();
            $index = tpp\isset_or($match[2], '');
            $raw = $line;
            return array($text, $index, $note, $raw);
        } else {
            return false;
        }
    }


    private function term_title_md() {
        $line = $this->_lines->cur();
        $this->_lines->test();
        $uline = $this->_lines->move()->cur();
        if (preg_match($this->term['md_title_ul'], $uline) > 0) {
            preg_match($this->term['md_title'], $line, $match);
            $this->_lines->move();
            $text = trim(tpp\isset_or($match[3], ''));
            $note = $this->term_note();
            $index = tpp\isset_or($match[2], '');
            $raw = $line . "\n" . $uline;
            return array($text, $index, $note, $raw);
        } else {
            $this->_lines->reject();
            return false;
        }
    }

    /**
     * Always matches: no title
     */
    private function term_title_none() {
        $text = '';
        $note = $this->empty_note();
        $index = '';
        $raw = '';
        return array($text, $index, $note, $raw);
    }


    /*
     * <project> ::= (task note* | info note* | empty* )*
     */
    private function term_project() {
        $matched = false;
        $project = array();

        $line = $this->_lines->cur();

        // special default project zero, for orphaned tasks and the like
        if ($this->_index == 0) {
            $text = \tpp\lang('orphaned');
            $note = $this->empty_note();
            $matched = true;
            $line = '';

        } elseif (preg_match($this->term['project'], $line , $match) > 0) {
            $this->_lines->move();

            $text = $match[1];
            $note = $this->term_note();
            $matched = true;
        }

        if ($matched) {
            $project = (object) array('type' => 'project', 'text' => $text, 'index' => $this->_index, 'note' => $note, 'raw' => $line);
            $this->_index++;

            $project->children = $this->match_any(array('term_task', 'term_info', 'term_empty'), array());
            return $project;
        } else {
            return false;
        }
    }


    /**
     * Try to match any term passed to each line, until a line fails to match.
     *
     * i.e. match ANY
     *
     * Return an array of matches
     *
     * @param array $terms  array of term functions as function names
     * @param mixed $empty  value to return if empty result
     * @return mixed|array    $empty | the matching results
     */
    private function match_any(Array $terms, $empty = false) {

        $result = array();
        while ($this->_lines->valid()) {
            $match = $this->match_any_one($terms, $empty);
            // if all terms fail then stop
            if ($match == $empty) break;
            // ignore empty lines
            if ( ! empty($match))  $result[] = $match;
        }

        if ( ! empty($result)) {
            return $result;
        } else {
            return $empty;
        }
    }


    private function match_any_one(Array $terms, $empty = false) {
        $result = $empty;
        foreach ($terms as $term) {
            $match = $this->$term();
            if ($match !== false) {
                $result = $match;
                break;
            }
        }
        return $result;
    }


    /**
     * task ::= [regex]
     */
    private function term_task() {

        $line = $this->_lines->cur();

        if (preg_match($this->term['task'], $line) > 0) {
            $this->_lines->move();

            $task = $this->parse_task($line);
            $task->note = $this->term_note();

            return $task;
        } else {
            return false;
        }
    }

    /**
     * info ::= [regex]
     *
     * return array(type, text, note)
     */
    private function term_info() {

        $line = $this->_lines->cur();

        // info ::= [regex]
        if (preg_match($this->term['info'], $line, $match) > 0) {
            $this->_lines->move();

            $info = (object) array('type' => 'info', 'text' => $line, 'note' => '', 'raw' => $line);
            $info->note = $this->term_note();

            return $info;
        } else {
            return false;
        }
    }


    private function term_note() {
        $note = $this->match_any_one(array('term_block_note', 'term_indent_note'), $this->empty_note());
        return $note;
    }


    /**
     * Multiline matcher for notes.
     *
     * Notes can be multiline, beginning and ending with '..'.
     *
     * note ::= .. \n [any text] \n ..
     *
     * return array(text, len)  [len = line count]
     */
    private function term_block_note() {

        $text = '';
        $len = 0;
        $raw = '';

        $prefix = $this->term['note_prefix'];
        $lines = $this->_lines;
        $line = trim($lines->cur());
        $done = false;
        $note = false;

        // allow for spaces before the note indicator (some may prefer an indented syntax...)
        if ($line == $prefix) {
            $lines->test();
            $raw = $line;
            do {
                $line = $lines->move()->cur();
                if (trim($line) == $prefix) {
                    $raw .= "\n" . $prefix;
                    $done = true;
                    $lines->move();
                } else {
                    $text .= (empty($text)) ? $line : "\n" . $line;
                    $raw .= "\n" . $line;
                    $len++;
                }
            } while ($lines->valid() && ! $done);

            if ($done) {
                $note = (object) array('text' => $text, 'len' => $len, 'raw' => $raw);
            } else {
                $lines->reject();
            }
        }
        return $note;
    }


    /**
     * Matches indented notes (2-4 spaces currently)
     *
     * @return boolean | string Note text, incl. linebreaks
     */
    private function term_indent_note() {

        $text = $raw = $nl = '';
        $len = 0;
        $done = false;
        $lines = $this->_lines;

        do {
            $line = $lines->cur();
            if ($lines->valid() && preg_match($this->term['indent_note'], $line, $match) > 0) {
                if ( ! empty($text)) $nl = "\n";
                $text .= $nl . $match[1];
                $raw .= $nl . $match[0];
                $len++;
                $lines->move();
            } else {
                $done = true;
            }
        } while( ! $done);

        if ($len > 0) {
            $note = (object) array('text' => $text, 'len' => $len, 'raw' => $raw);
            return $note;
        } else {
            return false;
        }
    }


    /**
     * Term for empty (blank) lines.
     *
     * Returns an empty string on match, false on failure
     */
    private function term_empty() {

        $line = $this->_lines->cur();
        if (empty($line)) {
            // just skip empty lines
            $this->_lines->move();
            return '';
        } else {
            return false;
        }
    }

    private function empty_note() {
        return (object) array('text' => '', 'len' => 0, 'raw' => '');
    }


     /**
     * Parse a task match into its separate elements.
     *
     * Currently: String text, Bool done, Enum state, Array tags, DateTime date
     *
     * @global  array $token
     * @param   array $line    a matched task line to be parsed
     * @return  array
     */
    private function parse_task($task) {

        // defaults for things that could be missing
        $date = 0;
        $tags = array();
        // 0 = full match, 1 = done, 2 = text, 3 = action
        $match = array('', '', '', '');

        // First convert any =interval tags into real tags
        $task = $this->expand_interval_tags($task);

        $done = (bool) (strtolower($task[0]) == strtolower($this->term['done_prefix']));

        preg_match($this->term['split_task'], $task, $match);
        $action = strlen(trim($match[3]));
        if ($action > MAX_ACTION) {
            $action = MAX_ACTION;
        }
        $text = $match[2];

        if (preg_match($this->term['tag_date'], $text, $date_match) == 1) {
            $date = strtotime($date_match[1]);
            //remove the tags!
            $text = trim(preg_replace($this->term['tag_date'], '', $text));
        }

        if (preg_match_all($this->term['tag'], $text, $tag_matches) >= 1) {
            $tags = $tag_matches[1];
            //remove the tags
            $text = preg_replace($this->term['tag'], '', $text);
        }

        $text = trim($text);

        $task = (object) array('type' =>'task', 'text' => $text, 'done' => $done, 'tags' => $tags, 'action' => $action, 'date' => $date, 'raw' => $match[0], $note = '');

        return $task;
    }


    /**
     * Convert any interval tags (=today, =tomorrow, etc...) to real dates.
     *
     * @param string $raw A user-entered task to be checked for date intervals
     * @return string The expanded text
     */
    function expand_interval_tags($raw) {

        // find any tags
        preg_match_all($this->term['interval_tok'], $raw, $matches, PREG_SET_ORDER);
        // do they match a time period?
        foreach ($matches as $match) {
            $orig_tag = $match[0];
            $date = $this->_filter->interval_as_date($orig_tag);
            if ($date !== false) {
                $raw = preg_replace('/' . $orig_tag . '/', $this->term['tag_prefix'] . strftime(\tpp\config('date_format'), $date[1]), $raw);
            }
        }
        return $raw;
    }
}



/**
 * Class to iterate all lines from a text string.
 *
 * '\n' is assumed as the line separater
 */
class Lines {

    private $pos = 0;
    private $end = 0;
    private $start = 0;
    private $lines = array();

    /**
     * Build a new Lines object.
     *
     * @param string $plaintext   any plain text string, with newlines as line separators
     */
    function __construct($plaintext) {
        $this->lines = explode("\n", $plaintext);
        $this->pos = 0;
        $this->end = count($this->lines);
    }
    /**
     * Move forward in the text.
     *
     * @return $this (Chainable)
     */
    function move() {
        $this->pos++;
        return $this;
    }

    /**
     * Current value
     *
     * @return value | boolean false
     */

    function cur() {
        if ($this->valid()) {
            return $this->lines[$this->pos];
        } else {
            return false;
        }
    }
    /**
     * Reset current position back to beginning of list
     *
     * @return Lines    Chainable
     */
    function reset() {
        $this->pos = 0;
        return $this;
    }

    /**
     * True if the current position is valid (usable)
     *
     * @return boolean
     */
    function valid() {
        return ($this->pos < $this->end);
    }


    function test() {
        $this->start = $this->pos;
    }

    function reject() {
        $this->pos = $this->start;
    }
}