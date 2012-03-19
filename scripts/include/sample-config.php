<?php
/*
	Sample Configuration File

	Copy this file to config-settings.php
	
	This file should be read-only to the user running the ldapauthmanager scripts.
*/



/*
	API Configuration
*/
$config["api_url"]		= "http://example.com/ldapauthmanager/api";			// Application Install Location
$config["api_server_name"]	= "auth.example.com";
$config["api_auth_key"]		= "ultrahighsecretkey";



/*
	Log File Location

	(must be readable by the user running the ldapauthmanager_logpush script.
*/

$config["log_file"]		= "/var/log/ldap";


/*
	Lock File

	Used to prevent clashes when multiple instances are accidently run.
*/

$config["lock_file"]		= "/var/lock/ldapauthmanager_lock";





// force debugging on for all users + scripts
// (note: debugging can be enabled on a per-user basis by an admin via the web interface)
//$_SESSION["user"]["debug"] = "on";


?>
