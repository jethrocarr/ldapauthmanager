<?php
/*
	user/user-permissions-process.php

	Access:		ldapadmins only

	Assign/unassign the selected user to/from groups.
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


		$gid_existing	= $obj_user->load_data_groups();
		$gid_new	= array();


		// run through the groups
		$obj_ldap_groups				= New ldap_query;
		$obj_ldap_groups->connect();
		$obj_ldap_groups->srvcfg["base_dn"]		= "ou=Group,". $GLOBALS["config"]["ldap_dn"];

		if ($obj_ldap_groups->search("cn=*", array("gidnumber", "cn")))
		{
			// add items
			foreach ($obj_ldap_groups->data as $data_group)
			{
				if ($data_group["cn"][0])
				{
					if ($_POST["memberuid_". $data_group["gidnumber"][0] ] == "on")
					{
						// add group to new list
						$gid_new[] = $data_group["gidnumber"][0];

						// set session for error handling
						$_SESSION["error"]["memberuid_". $data_group["gidnumber"][0] ] = "on";
					}

				}
			}

		} // end if groups

	} // end if valid user ID


	

	//// PROCESS DATA ////////////////////////////


	if (error_check())
	{
		$_SESSION["error"]["form"]["user_groups"] = "failed";
		header("Location: ../index.php?page=user_management/user-permissions.php&id=". $obj_user->id);
		exit(0);
	}
	else
	{
		error_clear();

		/*
			UPDATE THE GROUPS

			We need to check which groups have changed and for each group that's changed, load the data
			modify and then change.
		
		*/


		// needed to prevent breakage on some versions of PHP
                if ($gid_existing == 0)
                {
			$gid_existing = array();
		}


		// generate list of affected GID numbers.
		$gid_affected	= array_merge($gid_existing, $gid_new);
		$gid_affected	= array_unique($gid_affected);


		foreach ($gid_affected as $gid)
		{
			// is the GID in both new and existing arrays? If so, nothing has changed.
			if (in_array($gid, $gid_existing) && in_array($gid, $gid_new))
			{
				// no change
			}
			else
			{
				// open group to edit
				$obj_group	= New ldap_auth_manage_group;
				$obj_group->id	= $gid;

				if ($obj_group->load_data())
				{
					// update 

					// check what sort of change took place.
					if (in_array($gid, $gid_existing))
					{
						// has been removed from group

						$memberuids			= $obj_group->data["memberuid"];
						$obj_group->data["memberuid"]	= array();

						foreach ($memberuids as $uid)
						{
							// add all uids other than the selected user back
							if ($uid != $obj_user->data["uid"])
							{
								$obj_group->data["memberuid"][] = $uid;
							}
						}
					}
					else
					{
						// has been added to group
						$obj_group->data["memberuid"][] = $obj_user->data["uid"];
					}


					// update group
					if (!$obj_group->update())
					{
						log_write("error", "process", "An unexpected error ocurred whilst attempting to update group data for group GID ". $obj_group->id);
					}
				}
				else
				{
					log_write("error", "process", "An unexpected error occured whilst attemping to load group data for group GID ". $obj_group->id);
				}
			}

		} // end of loop through groups


		// commit
		if (error_check())
		{
			log_write("error", "process", "An error occured whilst attempting to update group assignments.");
		}
		else
		{
			log_write("notification", "process", "User group assignment has been updated.");
		}


		// goto view page
		header("Location: ../index.php?page=user_management/user-permissions.php&id=". $obj_user->id);
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
