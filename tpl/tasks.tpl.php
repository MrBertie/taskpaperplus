<h1>
    <?php echo \tpp\lang('task_header'); ?>
    <span class="freq">(<?php echo $this->task_count; ?>)</span>
    <p class="sortable"><?php echo \tpp\lang('sortable'); ?></p>
</h1>

<ul id="sortable">
    <?php
    foreach ($this->tasks as $task) {
        if ($task->hidden()) continue;
        echo $this->mark_up_item($task, false);
    }
    ?>
</ul>