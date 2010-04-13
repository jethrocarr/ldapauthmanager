<?php
/*
	group/group-delete-process.php

	access: ldapadmins

	Deletes a group from the LDAP database.
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('ldapadmins'))
{
	/// LOAD INPUT DATA //////////////////////

	$obj_group		= New ldap_auth_manage_group;
	$obj_group->id		= security_form_input_predefined("int", "id_group", 1, "");


	if (!$obj_group->verify_id())
	{
		log_write("error", "process", "The group you have attempted to edit - ". $obj_group->id ." - does not exist in this system.");
	}
	else
	{
		// load existing data
		$obj_group->load_data();
	}


	// these exist to make error handling work right
	$data["groupname"]		= security_form_input_predefined("any", "groupname", 0, "");

	// confirm deletion
	$data["delete_confirm"]		= security_form_input_predefined("any", "delete_confirm", 1, "You must confirm the deletion");

	


		
	//// ERROR CHECKING ///////////////////////
			

	/// if there was an error, go back to the entry page
	if ($_SESSION["error"]["message"])
	{	
		$_SESSION["error"]["form"]["group_delete"] = "failed";
		header("Location: ../index.php?page=group_management/group-delete.php&id=". $obj_group->id);
		exit(0);
	}
	else
	{
		/*
			Delete User
		*/

		if (!$obj_group->delete())
		{
			log_write("error", "process", "A fatal error occured whilst attempting to delete group.");
		}
		else
		{
			log_write("notification", "process", "Successfully deleted the group");
		}


		// return to group list
		header("Location: ../index.php?page=group_management/groups.php");
		exit(0);
	}
}
else
{
	// group does not have perms to view this page/isn't logged on
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
