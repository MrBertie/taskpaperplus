<!-- reset password form -->
<form name="resetuser" method="post" id="resetuser" class="resetuser" enctype="application/x-www-form-urlencoded" action="login-reset">
    <div>
        <label for="username"><?php tpp\lang('username_lbl'); ?></label>
        <input name="username" id="username" type="text">
    </div>
    <input name="action" id="action" value="resetlogin" type="hidden">
    <div>
        <input name="submit" id="submit" value="<?php tpp\lang('resetlogin_lbl'); ?>" type="submit">
    </div>
</form>