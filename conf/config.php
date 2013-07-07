<?php
/**
 * default application config files
 *
 */


$config['website_url']      = "http://github.com/MrBertie/taskpaperplus";
// version numbers: <major>.<minor>.<changes>.<bugfixes>
$config['version_number']   = '1.1.0.0 | 2013-Jul-07';

// relative to App Base Path
$config['debug_file']       = 'logs/debug.txt';
$config['log_file']         = 'logs/log.txt';
$config['user_file']        = 'conf/users';
$config['data_dir']         = 'data/';  // default data dir
$config['deleted_dir']      = '_deleted/';
$config['cache_dir']        = '_cache/';

$config['hide_tips']        = false;    // hide all pop up tool tips
$config['edit_new_tab']     = true;     // open new tabs in 'edit' state

$config['title']            = 'Taskpaper+';
$config['default_active']   = 'tasks';
$config['date_format']      = "%d-%b-%Y";   // strftime formatting!  See php help files

$config['username_pattern'] = '^[a-zA-Z0-9]{2,32}$';
$config['password_pattern'] = '^[a-zA-Z0-9]{2,32}$';