<?php
/*
	This is the master application configuration file for ldapauthmanager,
	you should not make any changes here unless you are a developer.

	For normal configuration, please see config-settings.php or copy
	sample-config.php into place if it doesn't already exist.
*/

$GLOBALS["config"] = array();



/*
	Define Application Name & Versions
*/

// define the application details
$GLOBALS["config"]["app_name"]			= "ldapauthmanager";
$GLOBALS["config"]["app_version"]		= "1.0.1";

// define the schema version required
$GLOBALS["config"]["schema_version"]		= "20100324";



/*
	Apply required PHP settings
*/
ini_set('memory_limit', '32M');			// note that ldapauthmanager doesn't need much RAM apart from when
						// doing source diffs or graph generation.



/*
	Inherit User Configuration
*/
require("config-settings.php");



/*
	Fixed options

	Do not touch anything below this line
*/

// Initate session variables
if ($_SERVER['SERVER_NAME'])
{
	// proper session variables
	session_name("ldapauthmanager");
	session_start();
}
else
{
	// trick to make logging and error system work correctly for scripts.
	$GLOBALS["_SESSION"]	= array();
	$_SESSION["mode"]	= "cli";
}



/*
	Connect to databases
*/

require("database.php");


?>
