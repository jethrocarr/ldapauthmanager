<?php
/*
	user/user-delete-process.php

	access: ldapadmins

	Deletes a user account
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('ldapadmins'))
{
	/// LOAD INPUT DATA //////////////////////

	$obj_user		= New ldap_auth_manage_user;
	$obj_user->id		= security_form_input_predefined("int", "id_user", 1, "");


	if (!$obj_user->verify_id())
	{
		log_write("error", "process", "The user you have attempted to edit - ". $obj_user->id ." - does not exist in this system.");
	}
	else
	{
		// load existing data
		$obj_user->load_data();
	}


	// these exist to make error handling work right
	$data["username"]		= security_form_input_predefined("any", "username", 0, "");

	// confirm deletion
	$data["delete_confirm"]		= security_form_input_predefined("any", "delete_confirm", 1, "You must confirm the deletion");

	


		
	//// ERROR CHECKING ///////////////////////
			

	/// if there was an error, go back to the entry page
	if ($_SESSION["error"]["message"])
	{	
		$_SESSION["error"]["form"]["user_delete"] = "failed";
		header("Location: ../index.php?page=user_management/user-delete.php&id=". $obj_user->id);
		exit(0);
	}
	else
	{
		/*
			Delete User
		*/

		if (!$obj_user->delete())
		{
			log_write("error", "process", "A fatal error occured whilst attempting to delete user. No changes have been made.");
		}
		else
		{
			log_write("notification", "process", "Successfully deleted user account & associated group");
		}


		// return to user list
		header("Location: ../index.php?page=user_management/users.php");
		exit(0);
	}
}
else
{
	// user does not have perms to view this page/isn't logged on
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
