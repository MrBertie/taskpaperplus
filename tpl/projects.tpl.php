<div id="projects">
    <ol>
    <?php
    foreach ($this->projects as $project) {
        global $term;
        if ($project->is_empty()) continue;
        $index = $project->index();
        $text = $project->text();
        if ($index > 0) {
            $text = $index . $term['proj_sep']  . $text;
        }
        echo '<li id="' . $project->key() . '" data-index="' . $index . '" title="">' . $text . '</li>';
    }
    ?>
    </ol>
</div>