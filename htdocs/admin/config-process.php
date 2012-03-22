<?php
/*
	admin/config-process.php
	
	Access: ldapadmins only

	Updates the system configuration.
*/


// includes
include_once("../include/config.php");
include_once("../include/amberphplib/main.php");


if (user_permissions_get("ldapadmins"))
{
	////// INPUT PROCESSING ////////////////////////


	// fetch all the data
	$data["AUTO_INT_UID"]			= security_form_input_predefined("int", "AUTO_INT_UID", 1, "");
	$data["AUTO_INT_GID"]			= security_form_input_predefined("int", "AUTO_INT_GID", 1, "");
	$data["FEATURE_ZONES"]			= security_form_input_predefined("checkbox", "FEATURE_ZONES", 0, "");
	$data["FEATURE_RADIUS"]			= security_form_input_predefined("checkbox", "FEATURE_RADIUS", 0, "");
	$data["AUTH_USERPASSWORD_TYPE"]		= security_form_input_predefined("any", "AUTH_USERPASSWORD_TYPE", 1, "");

	$data["BLACKLIST_ENABLE"]		= security_form_input_predefined("any", "BLACKLIST_ENABLE", 0, "");
	$data["BLACKLIST_LIMIT"]		= security_form_input_predefined("int", "BLACKLIST_LIMIT", 1, "");
	
	$data["DATEFORMAT"]			= security_form_input_predefined("any", "DATEFORMAT", 1, "");
	$data["TIMEZONE_DEFAULT"]		= security_form_input_predefined("any", "TIMEZONE_DEFAULT", 1, "");

	$data["FEATURE_LOGS_ENABLE"]			= security_form_input_predefined("checkbox", "FEATURE_LOGS_ENABLE", 0, "");

	if ($data["FEATURE_LOGS_ENABLE"])
	{
		$data["FEATURE_LOGS_PERIOD"]		= security_form_input_predefined("int", "FEATURE_LOGS_PERIOD", 0, "");
		$data["LOG_RETENTION_PERIOD"]		= security_form_input_predefined("int", "LOG_RETENTION_PERIOD", 0, "");
		$data["LOG_UPDATE_INTERVAL"]		= security_form_input_predefined("int", "LOG_UPDATE_INTERVAL", 1, "");

		$data["LOG_RETENTION_CHECKTIME"]	= 0; // reset check time, so that the log retention processes run
	}
	else
	{
		$data["FEATURE_LOGS_CHECKTIME"]		= 0;
		$data["FEATURE_LOGS_PERIOD"]		= 0;
		$data["LOG_RETENTION_CHECKTIME"]	= 0;
		$data["LOG_UPDATE_INTERVAL"]		= "5";
	}



	if ($data["FEATURE_RADIUS"])
	{
		$data["FEATURE_RADIUS"]				= "enabled";
		$data["FEATURE_RADIUS_MIKROTIK"]		= security_form_input_predefined("checkbox", "FEATURE_RADIUS_MIKROTIK", 0, "");
		$data["FEATURE_RADIUS_MAXVENDOR"]		= security_form_input_predefined("int", "FEATURE_RADIUS_MAXVENDOR", 1, "");
		
		if ($data["FEATURE_RADIUS_MIKROTIK"])
		{
			$data["FEATURE_RADIUS_MIKROTIK"]	= "enabled";
		}
		else
		{
			$data["FEATURE_RADIUS_MIKROTIK"]	= "disabled";
		}
	}
	else
	{
		$data["FEATURE_RADIUS"] = "disabled";
	}
		


	//// PROCESS DATA ////////////////////////////


	if ($_SESSION["error"]["message"])
	{
		$_SESSION["error"]["form"]["config"] = "failed";
		header("Location: ../index.php?page=admin/config.php");
		exit(0);
	}
	else
	{
		$_SESSION["error"] = array();

		/*
			Start Transaction
		*/
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();

	
		/*
			Update all the config fields

			We have already loaded the data for all the fields, so simply need to go and set all the values
			based on the naming of the $data array.
		*/

		foreach (array_keys($data) as $data_key)
		{
			$sql_obj->string = "UPDATE config SET value='". $data[$data_key] ."' WHERE name='$data_key' LIMIT 1";
			$sql_obj->execute();
		}


		/*
			Commit
		*/

		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "process", "An error occured whilst updating configuration, no changes have been applied.");
		}
		else
		{
			$sql_obj->trans_commit();

			log_write("notification", "process", "Configuration Updated Successfully");
		}

		header("Location: ../index.php?page=admin/config.php");
		exit(0);


	} // if valid data input
	
	
} // end of "is user logged in?"
else
{
	// user does not have permissions to access this page.
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
