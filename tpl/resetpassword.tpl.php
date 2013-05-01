<!-- reset password form -->
<form name="'.$formname.'" method="post" id="'.$formname.'" class="'.$formclass.'" enctype="application/x-www-form-urlencoded" action="'.$formaction.'">
    <div>
        <label for="username">Username</label>
        <input name="username" id="username" type="text">
    </div>
    <input name="action" id="action" value="resetlogin" type="hidden">
    <div>
        <input name="submit" id="submit" value="Reset Password" type="submit">
    </div>
</form>