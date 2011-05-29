<?php
/*
	zone_management/zone-edit-process.php

	Access: ldapadmin users only

	Updates or creates a zone account based on the information provided to it.
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
	
	$origzone		= security_form_input_predefined("any", "origzone", 0, "");

	$obj_zone		= New ldap_auth_manage_zone;
	$obj_zone->data["cn"]	= security_form_input_predefined("any", "zonename", 1, "");


	if ($origzone)
	{
		$mode = "edit";
	}
	else
	{
		$mode = "add";
	}


	if ($mode == "add")
	{
		if ($obj_zone->verify_zonename( $obj_zone->data["cn"] ))
		{
			// name already exists
			log_write("error", "process", "The zone ". $obj_zone->data["cn"] ." already exists in this system!");

			error_flag_field("zonename");
		}
	}
	else
	{
		// see if the zonename has changed
		if ($origzone != $obj_zone->data["cn"])
		{
			// check if the new name is available
			if ($obj_zone->verify_zonename( $obj_zone->data["cn"] ))
			{
				log_write("error", "process", "The zone name you wish to use \"". $obj_zone->data["cn"] ."\" is already avaliable.");

				error_flag_field("zonename");
			}
		}
		else
		{
			// using existing name, nothing TODO
		}
	
		// load existing data
		$obj_zone->load_data();

	} // end if editing



	// basic fields
	$obj_zone->data["uniqueMember"]		= NULL;

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
				if ($_POST["uniqueMember_". $data_user["uid"][0] ] == "on")
				{
					// add user to zone
					$obj_zone->data["uniqueMember"][] = "uid=". $data_user["uid"][0] .",ou=People,". $GLOBALS["config"]["ldap_dn"];
					
					// set session for error handling
					$_SESSION["error"]["uniqueMember_". $data_user["uid"][0] ] = "on";
				}
			}
		}

	} // end if users assigned

	
	// at least one member must be set
	if (empty($obj_zone->data["uniqueMember"]))
	{
		log_write("error", "process", "At least one member must be assigned to the group.");
	}
	

	/*
		Execute or return to input page with error
	*/
	if (error_check())
	{
		if ($mode == "edit")
		{
			$_SESSION["error"]["form"]["zone_view"]	= "failed";
			header("Location: ../index.php?page=zone_management/zone-view.php&cn=". $origzone ."");
		}
		else
		{
			$_SESSION["error"]["form"]["zone_add"]	= "failed";
			header("Location: ../index.php?page=zone_management/zone-add.php");
		}

		exit(0);
	}
	else
	{
		$_SESSION["error"] = array();


		/*
			Update zone account details
		*/

		if ($obj_zone->update())
		{
			log_write("notification", "process", "Updated zone successfully");
		}
		else
		{
			log_write("error", "process", "An error occured whilst attempting to update zone record.");
		}


		/*
			Return
		*/

		header("Location: ../index.php?page=zone_management/zone-view.php&cn=". $obj_zone->data["cn"] ."");
		exit(0);


	} // if valid data input
	
	
} // end of "is zone logged in?"
else
{
	// zone does not have permissions to access this page.
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
