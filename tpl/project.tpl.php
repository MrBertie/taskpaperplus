<!--
Project list template
-->
<h1>
    <?php echo $this->header; ?>
    <p class="sortable"><?php echo \tpp\lang('sortable'); ?></p>
</h1>

<ul id="sortable">
    <?php
    foreach($this->tasks as $task) {
        echo $this->mark_up_item($task, false);
    }
    ?>
</ul>

