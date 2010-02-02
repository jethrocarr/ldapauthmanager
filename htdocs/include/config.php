<?php
/*
	Do not touch any values in this configuration file.

	All changes should be made in config-settings.php
*/

$GLOBALS["config"] = array();



/*
	Define Application Name & Versions
*/

// define the application details
$GLOBALS["config"]["app_name"]			= "ldapauthmanager";
$GLOBALS["config"]["app_version"]		= "1.0.0_beta_1";

// define the schema version required
$GLOBALS["config"]["schema_version"]		= "20100203";



/*
	Apply required PHP settings
*/
ini_set('memory_limit', '32M');			// note that ldapauthmanager doesn't need much RAM apart from when
						// doing source diffs or graph generation.



/*
	Inherit User Configuration & Database Connectivity
*/
require("config-settings.php");
require("database.php");

?>
