<?php
/*
	group/group-radius-process.php

	Access:		ldapadmins only

	Define radius attributes for the selected group.
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('ldapadmins'))
{
	////// INPUT PROCESSING ////////////////////////


	$obj_group		= New ldap_auth_manage_group;
	$obj_group->id		= security_form_input_predefined("int", "id_group", 0, "");

	$num_vendor_fields	= sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS_MAXVENDOR'");


	if (!$obj_group->verify_id())
	{
		log_write("error", "process", "The group you have attempted to edit - ". $obj_group->id ." - does not exist in this system.");
	}
	else
	{
		// load existing data
		$obj_group->load_data();

		// error handling stuff
		security_form_input_predefined("any", "groupname", 0, "");

		// standard radius attributes
		$radius_attributes = radius_attr_standard();

		foreach ($radius_attributes as $attribute)
		{
			// unset any current values
			unset($obj_group->data[ $attribute ]);

			// fetch the new values
			$tmp = stripslashes(security_form_input_predefined("any", $attribute, 0, ""));

			if (!empty($tmp))
			{
				$obj_group->data[ $attribute ] = $tmp;
			}
		}



		// vendor attributes
		$obj_group->data["radiusCheckItem"] = NULL;
		$obj_group->data["radiusReplyItem"] = NULL;

		for ($i=0; $i < $num_vendor_fields; $i++)
		{
			$tmp = stripslashes(security_form_input_predefined("any", "vendor_attr_check_$i", 0, ""));
			if (!empty($tmp))
			{
				$obj_group->data["radiusCheckItem"][] = $tmp;
			}

			$tmp = stripslashes(security_form_input_predefined("any", "vendor_attr_reply_$i", 0, ""));
			if (!empty($tmp))
			{
				$obj_group->data["radiusReplyItem"][] = $tmp;
			}
		}

	} // end if valid group ID


	// verify that the feature is currently enabled
	if (sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS' LIMIT 1") == "disabled")
	{
		log_write("error", "process", "Radius attribute configuration has been disabled by the administrator. Use the admin configuration to page to enable it if required.");
	}
	

	//// PROCESS DATA ////////////////////////////


	if (error_check())
	{
		$_SESSION["error"]["form"]["group_radius"] = "failed";
		header("Location: ../index.php?page=group_management/group-radius.php&id=". $obj_group->id);
		exit(0);
	}
	else
	{
		/*
			Apply Changes
		*/
		error_clear();

		if (!$obj_group->update())
		{
			log_write("error", "process", "An error occured whilst attempting to update radius attributes.");
			print_r($obj_group->data);
			die("wtf");
		}
		else
		{
			log_write("notification", "process", "Group radius attributes have been updated.");
		}


		// goto view page
		header("Location: ../index.php?page=group_management/group-radius.php&id=". $obj_group->id);
		exit(0);


	} // if valid data input
	
	
} // end of "is group logged in?"
else
{
	// group does not have permissions to access this page.
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
