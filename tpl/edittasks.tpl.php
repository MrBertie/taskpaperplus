<?php
   $buttons = '<input type="image" src="images/save.png" class="icon save-button" value="Save" title="' . tpp\lang('save_changes_tip') . '">' . "\n" .
    '<input type="image" src="images/cancel.png" class="icon cancel-button" value="Cancel" title="' . tpp\lang('cancel_changes_tip') . '">';
?>

<div>
    <?php echo $buttons; ?>
</div>

<div class="find-replace-bar">
    <label><?php echo tpp\lang('find_lbl'); ?></label>
    <input type="text" id="find-word">
    <label><?php echo tpp\lang('replace_lbl'); ?></label>
    <input type="text" id="replace-word">
    <input type="button" value="<?php echo tpp\lang('go_lbl'); ?>" id="replace-button">
</div>

<textarea></textarea>

<div>
    <?php echo $buttons; ?>
</div>
