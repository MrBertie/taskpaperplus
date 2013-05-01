<!-- new user form -->
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
    <div>
        <label for="username">Username</label>
        <input name="username" id="username" type="text">
    </div>
    <div>
        <label for="password">Password</label>
        <input name="password" id="password1" type="password">
    </div>
    <div>
        <label for="password">Confirm Password</label>
        <input name="password" id="password2" type="password">
    </div>
    <input name="action" id="action" value="login" type="hidden">
    <div>
        <input name="submit" id="submit" value="Create" type="submit">
    </div>
</form>
