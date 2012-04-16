<!--
Project list template
-->
<?php include('tpl/tasktools.tpl.php'); ?>
<h1><?php echo $project_header; ?><p class="can-sort"><?php echo lang('can_sort'); ?></p></h1>
<div id="tasks">
<?php
foreach($project_tasks as $key => $task) {
    echo mark_up_task($task, $key, true);
}
?>
</div>

