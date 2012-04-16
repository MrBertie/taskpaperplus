<!--
The find results list template
-->
<?php include('tpl/tasktools.tpl.php'); ?>
<h1><span class="just-info"><?php echo $result_header; ?></span><?php echo ' ' . $search_expression; ?></h1>
<div id="tasks">
<?php
if(!empty($project_results)) {
    echo '<h2>' . $project_header .'<span class="freq">(' . $project_count . ')</span></h2>';
    foreach($project_results as $project) {
        $markup = mark_up_task($project, false);
        echo $markup;
    }
    echo '<br />';
}
?>
<h2><?php echo $task_header; ?><span class="freq">(<?php echo $task_count; ?>)</span></h2>
<?php
if(!empty($task_results)) {
    foreach($task_results as $task) {
        $markup = mark_up_task($task, true);
        echo $markup;
    }
}
?>
</div>
<br>