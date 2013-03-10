<input type="image" src="icons/edit.png" class="tool-icon" id="edit-button" value="Edit" title="<?php echo \tpp\lang('edit_all_tip'); ?>">
<?php if ( ! $this->restricted) { ?>
    <input type="image" src="icons/rename.png" class="tool-icon" id="rename-button" value="Rename" title="<?php echo \tpp\lang('rename_tip'); ?>">
    <input type="image" src="icons/remove.png" class="tool-icon" id="remove-button" value="Remove" title="<?php echo \tpp\lang('remove_tip'); ?>">
    <input type="image" src="icons/archive.png" class="tool-icon" id="archive-done-button" value="Archive" title="<?php echo \tpp\lang('archive_done_tip'); ?>">
<?php } ?>
<input type="image" src="icons/star-off.png" class="tool-icon" id="remove_actions-button" value="Star" title="<?php echo \tpp\lang('remove_actions_tip'); ?>">