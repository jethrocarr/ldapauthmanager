<?php
/*
	user/user-radius-process.php

	Access:		ldapadmins only

	Define radius attributes for the selected user.
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


	if (!$obj_user->verify_id())
	{
		log_write("error", "process", "The user you have attempted to edit - ". $obj_user->id ." - does not exist in this system.");
	}
	else
	{
		// load existing data
		$obj_user->load_data();

		// error handling stuff
		security_form_input_predefined("any", "username", 0, "");

		// standard radius attributes
		$radius_attributes = radius_attr_standard();

		foreach ($radius_attributes as $attribute)
		{
			// unset any current values
			unset($obj_user->data[ $attribute ]);

			// fetch the new values
			$tmp = stripslashes(security_form_input_predefined("any", $attribute, 0, ""));

			if (!empty($tmp))
			{
				$obj_user->data[ $attribute ] = $tmp;
			}
		}



		// vendor attributes
		$obj_user->data["radiusCheckItem"] = NULL;
		$obj_user->data["radiusReplyItem"] = NULL;

		for ($i=0; $i < 5; $i++)
		{
			$tmp = stripslashes(security_form_input_predefined("any", "vendor_attr_check_$i", 0, ""));
			if (!empty($tmp))
			{
				$obj_user->data["radiusCheckItem"][] = $tmp;
			}

			$tmp = stripslashes(security_form_input_predefined("any", "vendor_attr_reply_$i", 0, ""));
			if (!empty($tmp))
			{
				$obj_user->data["radiusReplyItem"][] = $tmp;
			}
		}

	} // end if valid user ID


	// verify that the feature is currently enabled
	if (sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS' LIMIT 1") == "disabled")
	{
		log_write("error", "process", "Radius attribute configuration has been disabled by the administrator. Use the admin configuration to page to enable it if required.");
	}
	

	//// PROCESS DATA ////////////////////////////


	if (error_check())
	{
		$_SESSION["error"]["form"]["user_radius"] = "failed";
		header("Location: ../index.php?page=user_management/user-radius.php&id=". $obj_user->id);
		exit(0);
	}
	else
	{
		/*
			Apply Changes
		*/
		error_clear();

		if (!$obj_user->update())
		{
			log_write("error", "process", "An error occured whilst attempting to update radius attributes.");
			print_r($obj_user->data);
			die("wtf");
		}
		else
		{
			log_write("notification", "process", "User's radius attributes have been updated.");
		}


		// goto view page
		header("Location: ../index.php?page=user_management/user-radius.php&id=". $obj_user->id);
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
