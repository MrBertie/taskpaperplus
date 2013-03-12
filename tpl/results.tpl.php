<!--
The find results list template
-->
<h1>
    <span class="just-info"><?php echo $this->header; ?></span>
    <?php echo ' ' . $this->search_expr; ?>
</h1>

<?php if ($this->project_count > 0) { ?>

<h2>
    <?php echo \tpp\lang('project_header'); ?>
    <span class="freq">(<?php echo $this->project_count; ?>)</span>
</h2>

<ul>
    <?php
    foreach($this->projects as $project) {
        $markup = $this->mark_up_item($project, false);
        echo $markup;
    }
    ?>
</ul>

<br />

<h2>
    <?php echo \tpp\lang('task_header'); ?>
    <span class="freq">(<?php echo $this->task_count; ?>)</span>
</h2>

<?php } ?>

<ul>
    <?php
    foreach($this->tasks as $task) {
        $markup = $this->mark_up_item($task, true);
        echo $markup;
    }
    ?>
</ul>