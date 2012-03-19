<?php
/*
	This is the master application configuration file for ldapauthmanager
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
$GLOBALS["config"]["app_version"]		= "1.3.0";


/*
	Initate session variables
*/

// trick to make logging and error system work correctly for scripts.
$GLOBALS["_SESSION"]	= array();
$_SESSION["mode"]	= "cli";


/*
	Inherit User Configuration
*/
require("config-settings.php");



?>
