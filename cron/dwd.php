#!/usr/bin/php -q
<?php

require('../conf/config.php');

new horizonLoader();
try {
	horizonMySQL::getInstance(array(
		'user' => DBUSER,
		'password' => DBPASSWORD,
		'host' => DBHOST,
		'database' => DATABASE,
		'options' => array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		)
	));

	$clsFeed = new dwdCapFeed(IMPORTDIR);
	$ret = $clsFeed->import();
	if ($ret === false){
		throw new Exception("Could not download data from Opendata Server",1000);
	}
} catch (Exception $e) {
	horizonException::handleException($e);
}

?>
