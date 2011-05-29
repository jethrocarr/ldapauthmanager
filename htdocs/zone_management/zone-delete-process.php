<?php
/*
	zone/zone-delete-process.php

	access: ldapadmins

	Deletes a zone from the LDAP database.
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('ldapadmins'))
{
	/// LOAD INPUT DATA //////////////////////

	$obj_zone		= New ldap_auth_manage_zone;
	$obj_zone->data["cn"]	= security_form_input_predefined("any", "origzone", 1, "");


	if (!$obj_zone->verify_zonename( $obj_zone->data["cn"] ))
	{
		log_write("error", "process", "The zone you have attempted to edit - ". $obj_zone->data["cn"] ." - does not exist in this system.");
	}
	else
	{
		// load existing data
		$obj_zone->load_data();
	}


	// these exist to make error handling work right
	$data["zonename"]		= security_form_input_predefined("any", "zonename", 0, "");

	// confirm deletion
	$data["delete_confirm"]		= security_form_input_predefined("any", "delete_confirm", 1, "You must confirm the deletion");

	


		
	//// ERROR CHECKING ///////////////////////
			
	if (error_check())
	{	
		$_SESSION["error"]["form"]["zone_delete"] = "failed";
		header("Location: ../index.php?page=zone_management/zone-delete.php&cn=". $obj_zone->data["cn"]);
		exit(0);
	}
	else
	{
		/*
			Delete Zone
		*/

		if (!$obj_zone->delete())
		{
			log_write("error", "process", "A fatal error occured whilst attempting to delete zone.");
		}
		else
		{
			log_write("notification", "process", "Successfully deleted the zone");
		}


		// return to zone list
		header("Location: ../index.php?page=zone_management/zones.php");
		exit(0);
	}
}
else
{
	// zone does not have perms to view this page/isn't logged on
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
