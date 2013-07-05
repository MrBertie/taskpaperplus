<!DOCTYPE html>
<html>
    <head>
        
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>
            <?php echo \tpp\config('title'); ?>
        </title>

        <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
        <script type="text/javascript" src="./lib/jquery.js"></script>
        <script type="text/javascript" src="./js/login.js"></script>
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
        
    </head>
    
    <body>
        
        <div class="logo">
            <p class="red">Task</p><p>paper+</p>
        </div>
        
        <div id="login-msg">
            <p>
                <?php echo \tpp\lang('login_msg'); ?>
            </p>
        </div>
        
        <div id="login-form">
            
            <ul class="tabs">

                <li id="login" class="selected">
                    <a href='#'><?php echo \tpp\lang('login_lbl'); ?></a>
                </li>

                <li id="register">
                    <a href='#'><?php echo \tpp\lang('register_lbl'); ?></a>
                </li>
            </ul>

            <form id="login" action="index.php" method="post" accept-charset="utf-8">
                <fieldset class="inputs">
                    <input class="username" name="username" type="text" placeholder="<?php echo \tpp\lang('username_lbl'); ?>" autofocus required pattern="<?php echo \tpp\config('username_pattern') ?>">   
                    <input class="password" name="password" type="password" placeholder="<?php echo \tpp\lang('password_lbl'); ?>" autocomplete="off" required pattern="<?php echo \tpp\config('password_pattern') ?>">
                </fieldset>
                <fieldset class="actions">
                    <input class="submit" type="submit" name="submit-login" value="<?php echo \tpp\lang('login_lbl'); ?>">
                    <a id="forgot-password" href="#"><?php echo \tpp\lang('forgotpassword_lbl'); ?></a>
                </fieldset>
            </form>

            <form id="register" action="index.php" method="post" accept-charset="utf-8">
                <fieldset class="inputs">
                    <input class="username" name="username" type="text" placeholder="<?php echo \tpp\lang('username_lbl'); ?>" autofocus required pattern="<?php echo \tpp\config('username_pattern') ?>">   
                    <input class="email" name="email" type="text" placeholder="<?php echo \tpp\lang('email_lbl'); ?>" required>   
                    <input class="password" name="password1" type="password" placeholder="<?php echo \tpp\lang('password_lbl'); ?>" autocomplete="off" required pattern="<?php echo \tpp\config('password_pattern') ?>">
                    <input class="password" name="password2" type="password" placeholder="<?php echo \tpp\lang('repeatpassword_lbl'); ?>" autocomplete="off" required pattern="<?php echo \tpp\config('password_pattern') ?>">
                </fieldset>
                <fieldset class="actions">
                    <input class="submit" type="submit" name="submit-register" value="<?php echo \tpp\lang('register_lbl'); ?>">
                </fieldset>

            </div>
        </div>
        
        <div id="login-err">
            <ul>
                <!-- filled via ajax -->
            </ul>
        </div>
        
    </body>
</html>
