<?php include('tpl/tasktools.tpl.php'); ?>
<h1><?php echo $task_header; ?><span class="freq">(<?php echo $task_count; ?>)</span><p class="can-sort"><?php echo lang('can_sort'); ?></p></h1>
<div id="tasks">
<?php
if ($tasks[0] instanceof TaskItem) {
    echo '<h3 id="0">' . $projectless . '</h3>';
}
foreach ($tasks as $task) {
    echo mark_up_task($task, false, true);
}
?>
</div>