<p><?php echo \tpp\lang('logged_in_as_lbl') . ' &nbsp' . $this->logged_in_as; ?></p>
<a class="logout" href='#' id="logout"><?php echo \tpp\lang('logout_lbl'); ?></a>

<a href="help/help.html"><?php echo \tpp\lang('help_lbl'); ?></a>
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
<span class="lang"><?php echo \tpp\lang('language'); ?></span>