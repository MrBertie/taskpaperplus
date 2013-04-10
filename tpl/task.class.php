<?php
namespace tpp\view;
use tpp\model;

/**
 * Special Task template includes markup functions.
 *
 * Use this when you need access to the project/tasks/notes/tag markup functions
 */
class TaskTemplate extends Template {

    function mark_up_item(model\BasicItem $item, $show_project = false) {
        global $term;

        $markup = '';
        list($li, $text, $note) = $this->basic_markup($item);

        if ($item instanceof model\TaskItem) {
            $colours = \tpp\lang('state_colours');
            $done = '';
            $tags = '';
            $date_tag = '';
            $checked = '';
            $decorate = '';
            $project = '';

            // done always takes precedence; however if not-done, the old state returns
            if ($item->done()) {
                $done = 'strike';
                $checked = ' checked="checked"';
                $decorate = ' class="' . $done . '"';
            } else {
                $decorate = ' class="bk-' . $colours[$item->action()] .'"';
            }

            foreach ($item->tags() as $tag) {
                $tags .= '<span class="tag" title="">' . $tag . '</span>';
            }

            if ($item->date() != '') {
                $date_tag = '<span class="date-tag" title="">' . $item->date() . '</span>';
            }

            if ($show_project) {
                $name = $this->_h($item->project_name());
                $project = '<span class="project" id="' . $item->project_key() . '" title="' .
                           '" data-index="' . $item->project_index() . '">' .
                           $name . '</span>';
            }

            $src = ($item->done()) ? 'images/done.png' : 'images/todo.png';
            $check = '<input type="image" class="check-done" src="' . $src . '" id="'. $item->key() . '" title="">';
            //$check = '<input type="checkbox" name="'. $item->key() . '" ' . $checked . '>';

            $p = '<p' . $decorate . '>' . $text . '</p>';
            $markup = $li . $check . $p . $tags . $date_tag . $project . $note . '</li>';

        } elseif ($item instanceof model\ProjectItem) {
            $index = $item->index();
            $pfx = ($index > 0) ? $index . $term['proj_sep'] : '';
            $p = '<p data-index="' . $index . '" title="">' . $pfx . $text . '</p>';
            $markup = $li . $p . $note . '</li>';

        } elseif ($item instanceof model\InfoItem) {
            $markup = $li . $text . $note . '</li>';
        }

        log&&msg('building task:', $item->raw(), get_class($item), $markup);

        return $markup . "\n";
    }


    private function basic_markup(model\BasicItem $item) {
        $li = '<li class="' . $item->type() . ' editable" title="" id="' . $item->key() . '" name="'. $this->_h($item->raw()) . '">';
        $text = $this->mark_up_syntax($this->_h($item->text()));
        $note = $this->mark_up_note($item);
        return array($li, $text, $note);
    }

    function mark_up_note($item) {
        $text = $this->_h($item->note()->text);
        $multi = ($item->note()->len > 1);
        if ( ! empty($text)) {
            $note = '<ul>';
            if ($multi) {
                $full_text = str_replace("\n", '<br/>', $this->_h($text));
                $title = strtok($this->_h($text), "\n") .
                         '<span class="more">' . '▼' . '</span>';
                $note .= '<li class="note reveal" title="">'  .
                         '<p>' . $title . '</p></li>' .
                         '<li class="hidden-note reveal" title="">' .
                         '<p>' . $full_text . '</p></li>';
            } else {
                $note .= '<li class="note">' . $text . '</li>';
            }
            $note .= '</ul>';
            return $note;
        }
        return '';
    }


    function mark_up_syntax($text) {
        global $term;

        // preg_replace is an time-expensive function, avoid calling it if not necessary
        if (strpbrk($text, $term['format_chars']) !== false) {
            $text = preg_replace($term['underline'], '<u>$1</u>', $text);
            $text = preg_replace($term['bold'], '<strong>$1</strong>', $text);
            $text = preg_replace($term['italic'], '<em>$1</em>', $text);
            $text = preg_replace($term['hyperlink'], '<a href="$1" title="$1">$3</a>', $text);
        }
        return $text;
    }


    /**
     * A quick preset htmlentities functions'
     * @param string $text
     * @return string
     */
    function _h($text) {
        return htmlentities($text, ENT_COMPAT, 'UTF-8');
    }
}