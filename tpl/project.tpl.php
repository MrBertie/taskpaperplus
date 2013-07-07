<!--
Project list template
-->
<h1>
    <?php echo $this->header; ?>
    <p class="sortable" title="<?php echo \tpp\lang('sortable_tip'); ?>"><img src="images/sortable.png" alt='sortable'/></p>
</h1>

<ul id="sortable">
    <?php
    foreach($this->tasks as $task) {
        echo $this->mark_up_item($task, false);
    }
    ?>
</ul>

