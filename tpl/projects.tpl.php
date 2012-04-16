<div id="projects">
    <ol>
    <?php
    foreach ($projects as $key => $project) {
        if ($key == 0) {
            if ( ! empty($project)) {
                echo '<ul><li id="0">' . $project . '</li></ul>';
            }
        } else {
            echo '<li id="' . $key . '" title="' . lang('project_click_tip') . '">' . $project . '</li>';
        }
    }
    ?>
    </ol>
</div>