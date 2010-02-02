<?php
/*
	user_management/user-edit-process.php

	Access: ldapadmin users only

	Updates or creates a user account based on the information provided to it.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('ldapadmins'))
{
	////// INPUT PROCESSING ////////////////////////


	$obj_user		= New ldap_auth_manage_user;
	$obj_user->id		= security_form_input_predefined("int", "id_user", 0, "");


	// are we editing an existing user or adding a new one?
	if ($obj_user->id)
	{
		$mode = "edit";

		if (!$obj_user->verify_id())
		{
			log_write("error", "process", "The user you have attempted to edit - ". $obj_user->id ." - does not exist in this system.");
		}
		else
		{
			// load existing data
			$obj_user->load_data();
		
			// get orig IDs so we can check if safe to change
			$orig_uidnumber		= $obj_user->data["uidnumber"];
			$orig_gidnumber		= $obj_user->data["gidnumber"];
		}

		// basic fields
		$obj_user->data["cn"]			= security_form_input_predefined("any", "realname", 1, "");
		$obj_user->data["uid"]			= security_form_input_predefined("any", "username", 1, "");
		$obj_user->data["uidnumber"]		= security_form_input_predefined("int", "uidnumber", 3, "");
		$obj_user->data["gidnumber"]		= security_form_input_predefined("int", "gidnumber", 3, "");
		$obj_user->data["loginshell"]		= security_form_input_predefined("any", "loginshell", 1, "");
		$obj_user->data["homedirectory"]	= security_form_input_predefined("any", "homedirectory", 1, "");
	}
	else
	{
		$mode = "add";

		// basic fields
		$obj_user->data["cn"]			= security_form_input_predefined("any", "realname", 1, "");
		$obj_user->data["uid"]			= security_form_input_predefined("any", "username", 1, "");
		$obj_user->data["uidnumber"]		= security_form_input_predefined("int", "uidnumber", 0, "");
		$obj_user->data["gidnumber"]		= security_form_input_predefined("int", "gidnumber", 0, "");
		$obj_user->data["loginshell"]		= security_form_input_predefined("any", "loginshell", 1, "");
		$obj_user->data["homedirectory"]	= security_form_input_predefined("any", "homedirectory", 0, "");
	}




	///// ERROR CHECKING ///////////////////////


	// check password (if the user has requested to change it)
	if ($_POST["password"] || $_POST["password_confirm"])
	{
		$data["password"]		= security_form_input_predefined("any", "password", 1, "");
		$data["password_confirm"]	= security_form_input_predefined("any", "password_confirm", 1, "");

		if ($data["password"] != $data["password_confirm"])
		{
			$_SESSION["error"]["message"][]			= "Your passwords do not match!";
			$_SESSION["error"]["password-error"]		= 1;
			$_SESSION["error"]["password_confirm-error"]	= 1;
		}

		// generate new password if required		
		if ($data["password"])
		{
			$obj_user->data["userpassword_plaintext"]	= $data["password"];
		}
	}



	if ($mode == "edit")
	{
		// if it's changed, check if the user ID has been taken by another user or not
		if ($orig_uidnumber != $obj_user->data["uidnumber"])
		{
			$obj_user_check		= New ldap_auth_manage_user;
			$obj_user_check->id	= $obj_user->data["uidnumber"];

			if ($obj_user_check->verify_id())
			{
				log_write("error", "process", "The requested UID number is already in use by another user, please select a different one.");
				error_flag_field("uidnumber");
			}

			unset($obj_user_check);
		}


		// if it's changed, check if the group ID has been taken by another group or not
		/*
		if ($orig_gidnumber != $obj_user->data["gidnumber"])
		{
			$obj_group_check	= New ldap_auth_manage_group;
			$obj_group_check->id	= $obj_user->data["gidnumber"];

			if ($obj_group_check->verify_id())
			{
				log_write("error", "process", "The requested GID number is already in use by another group, please select a different one.");
				error_flag_field("gidnumber");
			}

			unset($obj_group_check);
		}
		*/
	}




	//// PROCESS DATA ////////////////////////////


	if (error_check())
	{
		if ($mode == "edit")
		{
			$_SESSION["error"]["form"]["user_view"]	= "failed";
			header("Location: ../index.php?page=user_management/user-view.php&id=". $obj_user->id ."");
		}
		else
		{
			$_SESSION["error"]["form"]["user_add"]	= "failed";
			header("Location: ../index.php?page=user_management/user-add.php");
		}

		exit(0);
	}
	else
	{
		$_SESSION["error"] = array();


		/*
			Update user account details
		*/

		if ($obj_user->update())
		{
			log_write("notification", "process", "Updated account details successfully");
		}
		else
		{
			log_write("error", "process", "An error occured whilst attempting to update user record.");
		}



		/*
			Return
		*/

		header("Location: ../index.php?page=user_management/user-view.php&id=". $obj_user->data["uidnumber"] ."");
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
