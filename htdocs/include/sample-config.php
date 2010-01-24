<?php
/*
	Sample Configuration File

	This file should be read-only by the httpd user. All other users should be denied.
*/



/*
	MySQL Database Configuration
*/
$config["db_host"] = "localhost";			// hostname of the MySQL server
$config["db_name"] = "myapp";				// database name
$config["db_user"] = "root";				// MySQL user
$config["db_pass"] = "";				// MySQL password (if any)


/*
	LDAP Database Configuration
*/
$config["ldap_host"]		= "localhost";					// hostname of the LDAP server
$config["ldap_post"]		= "389";					// LDAP server port
$config["ldap_manager_user"]	= "cn=Manager,dc=example,dc=amberdms,dc=com";	// LDAP manager
$config["ldap_manager_pwd"]	= "";







/*
	Fixed options

	Do not touch anything below this line
*/

// Initate session variables
if ($_SERVER['SERVER_NAME'])
{
	// proper session variables
	session_start();
}
else
{
	// trick to make logging and error system work correctly for scripts.
	$GLOBALS["_SESSION"]	= array();
	$_SESSION["mode"]	= "cli";
}

// Connect to the MySQL database
include("database.php");


// force debugging on for all users + scripts
// (note: debugging can be enabled on a per-user basis by an admin via the web interface)
// $_SESSION["user"]["debug"] = "on";


?>
