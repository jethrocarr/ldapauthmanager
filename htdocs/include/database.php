<?php
/*
	include/database.php

	Establishes connection to the MySQL database.
*/



// login to the database
$GLOBALS["config"]["db_app"] = mysql_connect($config["db_host"], $config["db_user"], $config["db_pass"]);
if (!$GLOBALS["config"]["db_app"])
	die("Unable to connect to DB:" . mysql_error());

// select the database
$db_selected = mysql_select_db($config["db_name"], $GLOBALS["config"]["db_app"]);
if (!$db_selected)
	die("Unable to connect to DB:" . mysql_error());



?>
