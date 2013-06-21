<!-- login form -->
<form name="existuser" method="post" id="existuser" class="existuser" enctype="application/x-www-form-urlencoded" action="login-exist">
    <div>
        <label for="username"><?php tpp\lang('username_lbl'); ?></label>
        <input name="username" id="username" type="text">
    </div>
    <div>
        <label for="password"><?php tpp\lang('password_lbl'); ?></label>
        <input name="password" id="password" type="password">
    </div>
    <input name="action" id="action" value="login" type="hidden">
    <div>
        <input name="submit" id="submit" value="<?php tpp\lang('login_lbl'); ?>" type="submit">
    </div>
</form>