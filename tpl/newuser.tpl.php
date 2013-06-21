<!-- new user form -->
<form name="newuser" method="post" id="newuser" class="newuser" enctype="application/x-www-form-urlencoded" action="login-new">
    <div>
        <label for="username"><?php tpp\lang('username_lbl'); ?></label>
        <input name="username" id="username" type="text">
    </div>
    <div>
        <label for="password"><?php tpp\lang('password_lbl'); ?></label>
        <input name="password" id="password1" type="password">
    </div>
    <div>
        <label for="password"><?php tpp\lang('confirmpassword_lbl'); ?></label>
        <input name="password" id="password2" type="password">
    </div>
    <input name="action" id="action" value="login" type="hidden">
    <div>
        <input name="submit" id="submit" value="<?php tpp\lang('createuser_lbl'); ?>" type="submit">
    </div>
</form>