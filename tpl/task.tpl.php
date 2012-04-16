<?php
/**
 * Used by various templates to markup tasks//notes/tags
 *
 */
function mark_up_task(BasicItem $task, $show_project = false, $show_sort_tip = false) {
    // cache lang strings to save time on each call; this function is called for
    // every task line!
    static $tips = array();
    if (empty($tips)) {
        log&&msg(__METHOD__, 'start building tasks');
        $tips['state_colours'] = lang('state_colours');
        $tips['tag_click_tip'] = lang('tag_click_tip');
        $tips['project_click_tip'] = lang('project_click_tip');
        $tips['archive_task_tip'] = lang('archive_task_tip');
        $tips['edit_in_place_tip'] = lang('edit_in_place_tip');
        $tips['mark_complete_tip'] = lang('mark_complete_tip');
        $tips['action_toggle_tip'] = lang('action_toggle_tip');
        $tips['delete_task_tip'] = lang('delete_task_tip');
        $tips['reveal_tip'] = lang('reveal_tip');
        $tips['sort_tip'] = ($show_sort_tip) ? lang('sort_tip') : '';
    }
    $done = '';
    $tags = '';
    $notes = '';
    $text = html_encode(trim($task->text()));    // trim, just in case user prefers to use spaces after '-' prefix
    if ($task instanceof TaskItem) {
        $text = mark_up_syntax($text);
        $archive_button = '';
        $checked = '';
        $state = 0;
        $decorate = '';
        // done always takes precedence; however if undone, the old state returns!
        if ($task->done() === true) {
            $done = 'strike';
            $checked = ' checked="checked"';
            $decorate = ' class="' . $done . '"';
        } else {
            $actions = $tips['state_colours'];
            $decorate = ' class="filter-' . $actions[$task->action()] .'"';
        }
        foreach ($task->tags() as $tag) {
            $tags .= '<span class="tag" title="' . $tips['tag_click_tip'] . '">' . $tag . '</span>';
        }
        $project_name = ($show_project === true) ? '<span class="project" id="' . $task->project_key() . '" title="' .
                        $tips['project_click_tip'] . '">' . html_encode($task->project_name()) . '</span>' : '';
        if (!$task->restricted()) {
            $archive_button = '<input type="image" class="archive-button" src="icons/archive.png" name="'. $task->key() .
                       '" title="' . $tips['archive_task_tip'] . '">';
        }
        // to allow use of jquery.editable
        $item   = '<li class="editable" title="' . $tips['edit_in_place_tip'] . $tips['sort_tip'] . '"' .
                  ' id="' . $task->key() . '" name="'. html_encode($task->plain()) . '">';
        $src = ($task->done()) ? 'icons/done.png' : 'icons/todo.png';
        $check = '<input type="image" class="check-done" src="' . $src . '" name="'. $task->key() . '" title="' . $tips['mark_complete_tip'] . '">';
        $buttons= '<span class="task-buttons">' .
                  '<input type="image" class="action-button" src="icons/star.png" name="' . $task->key() .
                  '" title="'. $tips['action_toggle_tip'] . '">' .
                  $archive_button .
                  '<input type="image" class="delete-button" src="icons/delete.png" name="'. $task->key() .
                   '" title="' . $tips['delete_task_tip'] . '">' .
                  '</span>';
        $text = '<p' . $decorate . '>' . $text . '</p>';
        $text   = $text . $tags . $project_name;
        if ($task->has_notes()) {
            $notes = '<ul>';
            foreach ($task->notes() as $note) {
                if ($note->type == BLOCK_NOTE) {
                    $br_text = str_replace("\n", '<br/>', html_encode($note->text));
                    $title= strtok(html_encode($note->text), "\n") . ' …';
                    $notes .= '<li class="reveal">'  .
                              '<span title="' . $tips['reveal_tip'] . '">' .
                              '<img src="icons/note.png"><p>' . $title . '</p></li>' .
                              '<div class="hidden-note">' . $br_text . '</span></div>';
                } else {
                    $notes .= '<li class="note">' . $note->text . '</li>';
                }
            }
            $notes .= '</ul>';
        }
        $text = $item . $check . $text . $buttons . $notes . '</li>';

    } elseif ($task instanceof ProjectItem) {
        $text = '<h3 id="' . $task->key() . '" title="' . $tips['project_click_tip'] . $tips['sort_tip'] . '">' . $task->ord_text() . '</h3>';
    } elseif ($task instanceof LabelItem) {
        $text = mark_up_syntax($text);
        $text = '<h4 id="' . $task->key() . '">' . $task->text() . '</h4>';
    }
    return $text . "\n";
}
function mark_up_syntax($text) {
    static $rgx = array();
    // cache the regexes to save microseconds on each task build
    if (empty($rgx)) {
        $rgx['underline'] = config('underline');
        $rgx['bold'] = config('bold');
        $rgx['italic'] = config('italic');
        $rgx['hyperlink'] = config('hyperlink');
        $rgx['format_chars'] = config('format_chars');
    }
    // preg_replace is an expensive function, avoid calling them if not necessary
    if (strpbrk($text, $rgx['format_chars']) !== false) {
        $text = preg_replace($rgx['underline'], '<u>$1</u>', $text);
        $text = preg_replace($rgx['bold'], '<strong>$1</strong>', $text);
        $text = preg_replace($rgx['italic'], '<em>$1</em>', $text);
        $text = preg_replace($rgx['hyperlink'], '<a href="$1" title="$1">$3</a>', $text);
    }
    return $text;
}
function html_encode($text) {
    return htmlentities($text, ENT_COMPAT, 'UTF-8');
}
?>
