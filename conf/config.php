<?php

die('Please edit config'); # remove this line

define('IMPORTDIR', '/tmp/import/dwd', false);
define('DBHOST', 'localhost', false);
define('DATABASE', 'dwd', false);
define('DBUSER', '<yourdatabaseuser>', false);
define('DBPASSWORD', '<yourdatabasepassword>', false);

$cwd = dirname(__FILE__);
$cwd = preg_replace("!(conf)$!","libs",$cwd);

require($cwd.'/hrzn/horizonLoader.inc.php');
require($cwd.'/cap/dwdCapFeed.inc.php');

?>