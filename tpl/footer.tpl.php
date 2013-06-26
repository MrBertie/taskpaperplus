<p class="version-number">Version: <?php echo \tpp\config('version_number') . (PRODUCTION==true ? '' : ' | DEVELOPMENT'); ?></p>
<a href="help/help.html"><?php echo \tpp\lang('help_lbl'); ?></a>
<a href="readme.md"><?php echo \tpp\lang('about_lbl'); ?></a>
<a href="help/faq.html"><?php echo \tpp\lang('faq_lbl'); ?></a>
<a href="<?php echo tpp\config('website_url'); ?>"><?php echo \tpp\lang('website_lbl'); ?></a>

<select name="lang-list">
    <?php
    echo '<option selected>' . $this->cur_lang . '</option>';
    foreach ($this->langs as $lang) {
        if ($lang != $this->cur_lang) {
            echo '<option value="' . $lang . '">' . $lang . '</option>';
        }
    }
    ?>
</select>

<span class="label"><?php echo \tpp\lang('language'); ?></span>
<?php if (SHOW_ERRORS) { ?>
    <span class="link" id="purge-session">Clear Session!</span>
    <span class="link" id="purge-cache">Clear Cache!</span>
<?php } ?>
