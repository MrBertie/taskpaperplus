<div class="task-tools">
    <input type="image" src="icons/edit.png" class="tool-icon" id="edit-button" value="Edit" title="<?php echo lang('edit_all_tip'); ?>">
    <?php if ( ! $restricted) { ?>
        <input type="image" src="icons/rename.png" class="tool-icon" id="rename-button" value="Rename" title="<?php echo lang('rename_tip'); ?>">
        <input type="image" src="icons/remove.png" class="tool-icon" id="remove-button" value="Remove" title="<?php echo lang('remove_tip'); ?>">
        <input type="image" src="icons/archive.png" class="tool-icon" id="archiveall-button" value="Archive" title="<?php echo lang('archive_all_tip'); ?>">
    <?php } ?>
    <input type="image" src="icons/star-off.png" class="tool-icon" id="noaction-button" value="Star" title="<?php echo lang('remove_action_tip'); ?>">
    <div class="just-info"><?php echo long_date();?>&nbsp;</div>
</div>
