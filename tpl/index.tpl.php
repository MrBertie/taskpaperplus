<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>
        <?php echo $title; ?>
    </title>
    <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/tabs.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <script type="text/javascript" src="./lib/jquery.js"></script>
    <script type="text/javascript" src="./lib/jquery-ui.js"></script>
    <script type="text/javascript" src="./lib/jquery.jeditable.js"></script>
    <script type="text/javascript" src="./lib/jquery.address.js"></script>
    <script type="text/javascript" src="./lib/jquery.hotkeys.js"></script>
    <script type="text/javascript" src="./scripts/ajax_events.js"></script>
</head>
<body>
    <div id="indicator"></div>
    <div id="frame">
        <?php include('tpl/banner.tpl.php'); ?>
        <div id="header">
            <div class="left">
                <img id="home" src="icons/logo.png" alt="taskpaper+" title="<?php echo lang('startpage_tip'); ?>">
            </div>
            <div class="right">
                <input type="text" id="search-box" accesskey="C" title="<?php echo lang('search_box_tip'); ?>"/>
                <label id="placeholder"><?php echo lang('placeholder'); ?></label>
                <a href="search-help.html" title="<?php echo lang('search_help_tip'); ?>">Help?</a>
                <img id="reset-search" src="icons/reset.png" alt="reset" title="<?php echo lang('clear_box_tip'); ?>">
            </div>
        </div>
        <div id="tabs">
            <?php echo $tab_view; ?>
        </div>
        <div class="colmask threecol">
            <div class="colmid">
                <div class="colleft">
                    <div class="tasks">
                        <div id="task-view">
                            <?php echo $task_view; ?>
                        </div>
                        <div id="edit-view">
                            <?php include('tpl/edittasks.tpl.php'); ?>
                            <?php include('tpl/cheatsheet.tpl.php'); ?>
                        </div>
                    </div>
                    <div class="projects">
                        <h1><?php echo $project_header; ?></h1>
                        <?php echo $project_view; ?>
                    </div>
                    <div class="tags">
                        <?php include('tpl/filters.tpl.php'); ?>
                        <h1><?php echo $tag_header; ?></h1>
                        <?php echo $tag_view; ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="footer">
            <p class="version-number">Version: <?php echo config('version_number'); ?></p>
            <a href="help.html"><?php echo lang('help_lbl'); ?></a>
            <a href="readme.txt"><?php echo lang('about_lbl'); ?></a>
            <a href="faq.html"><?php echo lang('faq_lbl'); ?></a>
            <a href="http://code.google.com/p/taskpaperplus/"><?php echo lang('website_lbl'); ?></a>
            <select name="lang-list">
                <?php
                echo '<option selected>' . $cur_lang . '</option>';
                foreach ($langs as $lang) {
                    if ($lang != $cur_lang) echo '<option value="' . $lang . '">' . $lang . '</option>';
                }
                ?>
            </select>
            <span class="label">Language</span>
            <span class="link" id="purge-session">Clear Session!</span>
            <span class="link" id="purge-cache">Clear Cache!</span>
        </div>
        <input id="alert-messages" type="hidden" value="<?php echo $alert_messages; ?>" />
        <input id="page-address" type="hidden" value="<?php echo $page_address; ?>" />
        <input id="page-load" type="hidden" value="true" />
        <input id="task-prefix" type="hidden" value="<?php echo config('task_prefix'); ?>" />
    </div>
	</body>
</html>