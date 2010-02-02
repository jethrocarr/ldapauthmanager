<?php
/*
	group_management/group-edit-process.php

	Access: ldapadmin users only

	Updates or creates a group account based on the information provided to it.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('ldapadmins'))
{
	/*
		Fetch Form Input
	*/

	$obj_group		= New ldap_auth_manage_group;
	$obj_group->id		= security_form_input_predefined("int", "id_group", 0, "");


	// are we editing an existing group or adding a new one?
	if ($obj_group->id)
	{
		$mode = "edit";

		if (!$obj_group->verify_id())
		{
			log_write("error", "process", "The group you have attempted to edit - ". $obj_group->id ." - does not exist in this system.");
		}
		else
		{
			// load existing data
			$obj_group->load_data();
		}

		// basic fields
		$obj_group->data["cn"]			= security_form_input_predefined("any", "groupname", 1, "");
		$obj_group->data["gidnumber"]		= security_form_input_predefined("int", "gidnumber", 3, "");
		$obj_group->data["memberuid"]		= NULL;

		// get member information
		$obj_ldap_users				= New ldap_query;
		$obj_ldap_users->connect();
		$obj_ldap_users->srvcfg["base_dn"]	= "ou=People,". $GLOBALS["config"]["ldap_dn"];

		if ($obj_ldap_users->search("uid=*", array("uid")))
		{
			// add items
			foreach ($obj_ldap_users->data as $data_user)
			{
				if ($data_user["uid"][0])
				{
					if ($_POST["memberuid_". $data_user["uid"][0] ] == "on")
					{
						// add user to group
						$obj_group->data["memberuid"][]	= $data_user["uid"][0];

						// set session for error handling
						$_SESSION["error"]["memberuid_". $data_user["uid"][0] ] = "on";
					}
				}
			}
		} // end if users


	}
	else
	{
		$mode = "add";

		// basic fields
		$obj_group->data["cn"]			= security_form_input_predefined("any", "groupname", 1, "");
		$obj_group->data["gidnumber"]		= security_form_input_predefined("int", "gidnumber", 0, "");
	}



	/*
		Perform Error Processing
	*/


	if ($mode == "edit")
	{
		// if it's changed, check if the group ID has been taken by another group or not
		if ($obj_group->id != $obj_group->data["gidnumber"])
		{
			$obj_group_check	= New ldap_auth_manage_group;
			$obj_group_check->id	= $obj_group->data["gidnumber"];

			if ($obj_group_check->verify_id())
			{
				log_write("error", "process", "The requested GID number is already in use, please select another.");
				error_flag_field("gidnumber");
			}

			unset($obj_group_check);
		}
	}


	

	/*
		Execute or return to input page with error
	*/
	if (error_check())
	{
		if ($mode == "edit")
		{
			$_SESSION["error"]["form"]["group_view"]	= "failed";
			header("Location: ../index.php?page=group_management/group-view.php&id=". $obj_group->id ."");
		}
		else
		{
			$_SESSION["error"]["form"]["group_add"]	= "failed";
			header("Location: ../index.php?page=group_management/group-add.php");
		}

		exit(0);
	}
	else
	{
		$_SESSION["error"] = array();


		/*
			Update group account details
		*/

		if ($obj_group->update())
		{
			log_write("notification", "process", "Updated group successfully");
		}
		else
		{
			log_write("error", "process", "An error occured whilst attempting to update group record.");
		}



		/*
			Return
		*/

		header("Location: ../index.php?page=group_management/group-view.php&id=". $obj_group->data["gidnumber"] ."");
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
