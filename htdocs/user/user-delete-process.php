<?php
/*
	user/user-delete-process.php

	access: admin

	Deletes a user account
*/

// includes
include_once("../include/config.php");
include_once("../include/amberphplib/main.php");


if (user_permissions_get('admin'))
{
	/////////////////////////

	$id				= security_form_input_predefined("int", "id_user", 1, "");

	// these exist to make error handling work right
	$data["username"]		= security_form_input_predefined("any", "username", 0, "");

	// confirm deletion
	$data["delete_confirm"]		= security_form_input_predefined("any", "delete_confirm", 1, "You must confirm the deletion");

	
	// make sure the user actually exists
	$sql_obj		= New sql_query;
	$sql_obj->string	= "SELECT id FROM `users` WHERE id='$id' LIMIT 1";
	$sql_obj->execute();
	
	if (!$sql_obj->num_rows())
	{
		$_SESSION["error"]["message"][] = "The user you have attempted to edit - $id - does not exist in this system.";
	}


		
	//// ERROR CHECKING ///////////////////////
			

	/// if there was an error, go back to the entry page
	if ($_SESSION["error"]["message"])
	{	
		$_SESSION["error"]["form"]["user_delete"] = "failed";
		header("Location: ../index.php?page=user/user-delete.php&id=$id");
		exit(0);
	}
	else
	{
		// start transaction
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();


		/*
			Delete User
		*/

		$sql_obj->string = "DELETE FROM users WHERE id='$id' LIMIT 1";
		$sql_obj->execute();


		/*
			Delete user permissions
		*/
		
		$sql_obj->string	= "DELETE FROM users_permissions WHERE userid='$id'";
		$sql_obj->execute();



		/*
			Delete user options
		*/
				
		$sql_obj->string	= "DELETE FROM users_options WHERE userid='$id'";
		$sql_obj->execute();



		/*
			Delete user <-> filter assignments
		*/

		$sql_obj->string	= "DELETE FROM filters_users WHERE id_user='$id'";
		$sql_obj->execute();


		// update configuration flag
		$sql_obj->string = "UPDATE config SET value='update_required' WHERE name='PROCMAIL_UPDATE_STATUS' LIMIT 1";
		$sql_obj->execute();


		// end transaction
		if ($_SESSION["error"]["message"])
		{
			log_write("error", "process", "A fatal error occured whilst attempting to delete user. No changes have been made.");

			$sql_obj->trans_rollback();
		}
		else
		{
			log_write("notification", "process", "Successfully deleted user account & preferences");

			$sql_obj->trans_commit();
		}


		// return to user list
		header("Location: ../index.php?page=user/users.php");
		exit(0);
	}

	/////////////////////////
	
}
else
{
	// user does not have perms to view this page/isn't logged on
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
