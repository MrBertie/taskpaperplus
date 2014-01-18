<!--
Project list template
-->
<h1>
    <?php echo \tpp\lang('project_title'); ?>
    <span class="freq">(<?php echo $this->task_count; ?>)</span>
</h1>

<ul>
    <?php
    foreach ($this->projects as $project) {
        echo $this->mark_up_item($project, false);
    }
    ?>
</ul>

<ul id="sortable">
    <?php
    foreach($this->tasks as $task) {
        echo $this->mark_up_item($task, false);
    }
    ?>
</ul>

