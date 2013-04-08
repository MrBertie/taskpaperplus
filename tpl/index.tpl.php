<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>
        <?php echo \tpp\config('title'); ?>
    </title>

    <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

    <script type="text/javascript" src="./lib/jquery.js"></script>
    <script type="text/javascript" src="./lib/jquery-ui-sortable.js"></script>
    <script type="text/javascript" src="./lib/jquery.jeditable.js"></script>
    <script type="text/javascript" src="./lib/jquery.address.js"></script>
    <script type="text/javascript" src="./lib/jquery.hotkeys.js"></script>
    <script type="text/javascript" src="./lib/taboverride.js"></script>
    <script type="text/javascript" src="./lib/jquery.taboverride.js"></script>
    <script type="text/javascript" src="./js/client.js"></script>

    <script id="jslang" type="application/json"><?php echo $this->jslang; ?></script>
</head>


<body>
    <?php include('tpl/banner.tpl.php'); ?>
    <div id="indicator"></div>

    <div id="frame">

        <div id="tabs">
            <?php echo $this->tabs; ?>
        </div>

        <div id="content">

            <div id="header">
                <?php echo $this->header; ?>
            </div>

            <div class="columns">
                <div class="projects column">
                    <h1><?php echo \tpp\lang('project_header'); ?></h1>
                    <?php echo $this->projects; ?>
                </div>

                <div class="tasks column">
                    <div id="view-tasks">
                        <?php echo $this->tasks; ?>
                    </div>
                    <div id="edit-tasks">
                        <?php include('tpl/edittasks.tpl.php'); ?>
                        <?php include('tpl/cheatsheet.tpl.php'); ?>
                    </div>
                </div>

                <div class="meta column">
                    <h1><?php echo \tpp\lang('filter_header'); ?></h1>
                    <?php echo $this->filters; ?>
                    <h1><?php echo \tpp\lang('tag_header'); ?></h1>
                    <?php echo $this->tags; ?>
                </div>
            </div>

            <div id="footer">
                <?php include('tpl/footer.tpl.php'); ?>
            </div>
        </div>

    </div>

    <input id="page-load"        type="hidden" value="true" />
    <input id="page-address"     type="hidden" value="<?php echo $this->page_address; ?>" />
    <input id="task-prefix"      type="hidden" value="<?php echo $this->task_prefix; ?>" />
    <input id="task-buttons-tpl" type="hidden" value='<?php echo $this->task_buttons; ?>' />
</body>
</html>