<!-- Tabbed bar -->
<ul class="tab"> 
    <?php
    foreach($file_names as $key => $file_name) {
        $tab = "<li";
        if ($file_name == FILE_ARCHIVE) {
            $class= 'icon-tab';
            $name = FILE_ARCHIVE;
            $display = '<img src="icons/archive-tab.png">';
            $tip = lang('archive_tab_tip');          
        } elseif ($file_name == FILE_TRASH) {
            $class= 'icon-tab';
            $name = FILE_TRASH;
            $display = '<img src="icons/deleted-tab.png">';
            $tip = lang('trash_tab_tip');
        } else {
            $class = '';
            $name = $display = $file_name;
            $tip = lang('change_tab_tip');
        }
        if($file_name == $active) {
            $class = 'active ' . $class;
            $name = $file_name;
            $tip = lang('reset_tab_tip');
        }
        $tab = '<li class="' . $class . '" name="' . $name . '" title="' . $tip . '">' .
                '<span class="tab"><span>' . $display . "</span></span></li>";
        echo $tab;
    }
    // the 'ADD NEW' tab at the end
    echo '<li class="icon-tab" name="__new__" title="' . lang('add_tab_tip') .
           '"><span class="tab"><span><img src="icons/add.png"></span></span></li>';
    ?>
</ul>
