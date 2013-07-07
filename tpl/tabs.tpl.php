<ul class="tabs">
    <?php
    $markup = '';
    foreach($this->tabs as $tab) {
        $tab_class = '';
        $text_class = 'fixed';
        $name = $tab->name;
        if ($tab->type == TAB_ARCHIVE) {
            $tip = \tpp\lang('archive_tab_tip');
            $display = \tpp\lang('archive_lbl'); 
        } elseif ($tab->type == TAB_TRASH) {
            $tip = \tpp\lang('trash_tab_tip');
            $display = \tpp\lang('trash_lbl'); 
        } else {
            $text_class = '';
            $tip = $tab->note->text;
            $display = $tab->title;
        }
        $tip .= ( ! empty($tip)) ? "\n――――\n" : '';

        if($tab->name == $this->active) {
            $tab_class = 'selected';
            $tip .= \tpp\lang('reset_tab_tip');
        } else {
            $tip .= \tpp\lang('change_tab_tip');
        }

        $markup .= '<li class="' . $tab_class . '" name="' . $name . '" title="' . $tip . '"><a href="#" class="' . $text_class . '">' . $display . '</a></li>' . PHP_EOL;
    }

    // the 'ADD NEW' tab at the end of the row
    $markup .= '<li name="__new__" title="' . \tpp\lang('new_tab_tip') .
           '"><a href="#" class="fixed"><img src="images/add.png"></a></li>' . PHP_EOL;
    $markup .= '<p class="today" title="">' . \tpp\long_date() . '</p>' . PHP_EOL;
    echo $markup;
    ?>
</ul>