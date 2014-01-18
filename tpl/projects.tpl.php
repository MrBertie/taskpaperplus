<ol id="sortable">
<?php
foreach ($this->projects as $project) {
    global $term;
    if ($project->empty_orphans()) continue;
    $index = $project->index();
    $text = $project->text();
    if ($index > 0) {
        $text = $index . $term['proj_sep']  . $text;
        $not_sortable = '';
    } else {
        $not_sortable = ' class="not-sortable"';
    }
    echo '<li id="' . $project->key() . '"' . $not_sortable . '" data-index="' . $index . '" title=""><p>' . $text . '</p></li>';
}
?>
</ol>