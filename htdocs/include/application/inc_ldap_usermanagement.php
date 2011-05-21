<?php
/*
	inc_ldap_usermanagement.php

	Core class for manipulating LDAP users and groups.

	(c) Copyright 2010 Amberms Ltd <legal@amberdms.com>
	
	Licensed under the GNU AGPL software license.
*/




/*
	CLASS LDAP_AUTH_MANAGE_USER

	Provides functions for quering, creating, updating and deleting
	authentication users from an LDAP database.
*/
class ldap_auth_manage_user
{
	var $obj_ldap;		// LDAP object

	var $id;		// ID of the user account to handle
	var $data;


	/*
		Constructor
	*/
	function ldap_auth_manage_user()
	{
		/*
			Init LDAP database connection
		*/
		$this->obj_ldap = New ldap_query;

		// connect to LDAP server
		if (!$this->obj_ldap->connect())
		{
			log_write("error", "user_auth", "An error occurred in the authentication backend, please contact your system administrator");
			return -1;
		}

		// set base_dn to run user lookups in
		$this->obj_ldap->srvcfg["base_dn"] = "ou=People,". $GLOBALS["config"]["ldap_dn"];
	}



	/*
		list_users

		Fetches a list of users with uidnumber and username information, saves into the $this->data array.

		Returns
		0	Failure to query LDAP
		1	Success
	*/
	function list_users()
	{
		log_debug("ldap_auth_manage_user", "Executing list_users()");


		// fetch all users
		if ($this->obj_ldap->search("uidnumber=*", array("uidnumber", "uid")))
		{
			log_debug("ldap_auth_manager_user", "Found a total of ". $this->obj_ldap->data_num_rows ." users");

			$this->data = array();

			for ($i=0; $i < $this->obj_ldap->data_num_rows; $i++)
			{
				// set values
				$this->data[$i]["uidnumber"]	= $this->obj_ldap->data[$i]["uidnumber"][0];
				$this->data[$i]["uid"]		= $this->obj_ldap->data[$i]["uid"][0];
			}

			return 1;
		}

		return 0;

	}




	/*
		verify_id

		Checks that the provided ID is a valid user

		Results
		0	Failure to find the ID
		1	Success - user exists
	*/

	function verify_id()
	{
		log_debug("ldap_auth_manage_user", "Executing verify_id()");

		if ($this->id)
		{
			// run query against users
			$this->obj_ldap->search("uidnumber=". $this->id, array("uidnumber"));

			if ($this->obj_ldap->data_num_rows)
			{
				return 1;
			}
			else
			{
				log_write("debug", "page", "Invalid user ". $this->id ." requested");
			}
		}

		return 0;

	} // end of verify_id



	/*
		verify_username

		Checks that the provided username belongs to a valid user and sets
		and returns the UID.

		Results
		0	Failure to find the ID
		#	User ID
	*/

	function verify_username($username)
	{
		log_debug("ldap_auth_manage_user", "Executing verify_username($username)");

		// run query against users
		$this->obj_ldap->search("uid=". $username, array("uidnumber"));

		if ($this->obj_ldap->data_num_rows)
		{
			$this->id = $this->obj_ldap->data[0]["uidnumber"][0];

			return $this->id;
		}
		else
		{
			log_write("debug", "page", "Invalid user ". $this->id ." requested");
		}

		return 0;

	} // end of verify_username


	/*
		load_data

		Load all the information regarding the selected user.

		Results
		0	Failure
		1	Success
	*/
	function load_data()
	{
		log_debug("ldap_auth_manage_user", "Executing load_data()");

		$this->data = array();

		// fetch all user attributes
		$this->obj_ldap->search("uidnumber=". $this->id);

		if ($this->obj_ldap->data_num_rows)
		{
			// set values
			$this->data["uid"]		= $this->obj_ldap->data[0]["uid"][0];
			$this->data["sn"]		= $this->obj_ldap->data[0]["sn"][0];
			$this->data["gn"]		= $this->obj_ldap->data[0]["givenname"][0];
			$this->data["uidnumber"]	= $this->obj_ldap->data[0]["uidnumber"][0];
			$this->data["gidnumber"]	= $this->obj_ldap->data[0]["gidnumber"][0];
			$this->data["loginshell"]	= $this->obj_ldap->data[0]["loginshell"][0];
			$this->data["homedirectory"]	= $this->obj_ldap->data[0]["homedirectory"][0];
			$this->data["userpassword"]	= $this->obj_ldap->data[0]["userpassword"][0];

			// fetch object classes - useful for when dealing with legacy users
			for ($i=0; $i < $this->obj_ldap->data[0]["objectclass"]["count"]; $i++)
			{
				$this->data["objectclass"][] = $this->obj_ldap->data[0]["objectclass"][$i];
			}
	
			// set radius values
			if (sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS' LIMIT 1") != "disabled")
			{
				// standard attributes
				$radius_attributes = radius_attr_standard();

				foreach ($radius_attributes as $attribute)
				{
					if (!empty($this->obj_ldap->data[0][ strtolower($attribute) ][0]))
					{
						$this->data[ $attribute ]		= $this->obj_ldap->data[0][ strtolower($attribute) ][0];
					}
				}


				// vendor specific: mikrotik
				if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
				{
					$radius_attributes = array_keys(radius_attr_mikrotik());

					foreach ($radius_attributes as $attribute)
					{
						if (!empty($this->obj_ldap->data[0][ strtolower($attribute) ][0]))
						{
							$this->data[ $attribute ]	= $this->obj_ldap->data[0][ strtolower($attribute) ][0];
						}
					}
				}
	

				// vendor specific: generic
				$num_vendor_fields = sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS_MAXVENDOR'");

				for ($i=0; $i < $num_vendor_fields; $i++)
				{
					if (!empty($this->obj_ldap->data[0]["radiuscheckitem"][$i]))
					{
						$this->data["radiusCheckItem"][$i]	= $this->obj_ldap->data[0]["radiuscheckitem"][$i];
					}

					if (!empty($this->obj_ldap->data[0]["radiusreplyitem"][$i]))
					{
						$this->data["radiusReplyItem"][$i]	= $this->obj_ldap->data[0]["radiusreplyitem"][$i];
					}
				}

			}

			return 1;
		}

		return 0;
	}


	/*
		load_data_groups

		Returns GID of all groups the user belongs to

		Results
		0	Failure
		array	GIDs of groups that user belongs to
	*/
	function load_data_groups()
	{
		log_debug("ldap_auth_manage_user", "Executing load_data_groups()");

		// fetch all group attributes
		$this->obj_ldap->srvcfg["base_dn"] = "ou=Group,". $GLOBALS["config"]["ldap_dn"];
		$this->obj_ldap->search("memberuid=". $this->data["uid"], array("gidnumber"));


		if ($this->obj_ldap->data_num_rows)
		{
			$return_gid = array();

			for ($i=0; $i < $this->obj_ldap->data["count"]; $i++)
			{
				$return_gid[]	= $this->obj_ldap->data[$i]["gidnumber"][0];
			}

			// reset base_dn
			$this->obj_ldap->srvcfg["base_dn"] = "ou=People,". $GLOBALS["config"]["ldap_dn"];

			// return array of GIDs
			return $return_gid;
		}


		// reset base_dn
		$this->obj_ldap->srvcfg["base_dn"] = "ou=People,". $GLOBALS["config"]["ldap_dn"];

		return 0;
	}






	/*
		create

		Creates a new LDAP user (and the associated group). This function is typically
		called automatically by the update() function.

		Results
		0	Failure
		#	Success - returns ID of user7
	*/
	function create()
	{
		log_write("debug", "ldap_auth_manage_user", "Executing create()");


		/*
			Calculate uid/gid (if needed)
		*/

		if (!$this->data["uidnumber"] || !$this->data["gidnumber"])
		{
			$this->id				= $this->create_unique_id();

			if (!$this->data["uidnumber"])
			{
				$this->data["uidnumber"]	= $this->id;
			}

			if (!$this->data["gidnumber"])
			{
				$this->data["gidnumber"]	= $this->id;
			}
		}
		else
		{
			$this->id			= $this->data["uidnumber"];
		}


		/*
			Create new LDAP user object
		*/
		
		// set objectclasses
		$this->data["objectclass"]	= NULL;
		$this->data["objectclass"][]	= "top";
		$this->data["objectclass"][]	= "inetOrgPerson";
		$this->data["objectclass"][]	= "posixAccount";
		$this->data["objectclass"][]	= "shadowAccount";

		// if radius is enabled, add the radius profile schema
		if ($GLOBALS["config"]["FEATURE_RADIUS"] != "disabled")
		{
			// standard attributes
			$this->data["objectclass"][]		= "radiusprofile";

			// vendor specific: mikrotik
			if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
			{
				$this->data["objectclass"][]	= "radiusMikrotik";
			}
		}


		// set the CN from the name
		$this->data["cn"] = $this->data["gn"] ." ". $this->data["sn"];

		// password placeholder
		switch ($GLOBALS["config"]["AUTH_USERPASSWORD_TYPE"])
		{
			case "CLEAR_SIMPLE":
				$this->data["userpassword"]	= "";		// must be blank: "x" is a matchable password
			break;

			case "CLEAR_HEADER":
				$this->data["userpassword"]	= "";		// must be blank: "x" is a matchable password
			break;

			case "SSHA":	
				$this->data["userpassword"]	= "{SSHA}x";
			break;
		}

		// set home directory if not provided
		if (!$this->data["homedirectory"])
		{
			$this->data["homedirectory"] = "/home/". $this->data["uid"];
		}



		// set DN
		$this->obj_ldap->record_dn = "uid=". $this->data["uid"];

		// create record
		$this->obj_ldap->data = $this->data;
		unset($this->obj_ldap->data["userpassword_plaintext"]);	// remove this attribute to prevent errors

		if (!$this->obj_ldap->record_create())
		{
			return 0;
		}



		/*
			Check if a group exists with the provided GID, if it doesn't, then
			we should create it.
		*/

		$obj_group	= New ldap_auth_manage_group;
		$obj_group->id	= $this->data["gidnumber"];

		if ($obj_group->verify_id())
		{
			// group exists
			log_write("debug", "ldap_auth_manage_user", "A group with ID of ". $obj_group->id ." already exists, will not create another");
		}
		else
		{
			// no group exists.
			// create a new group
		
			$obj_group	= New ldap_auth_manage_group;

			$obj_group->data["cn"]			= $this->data["uid"];
			$obj_group->data["gidnumber"]		= $this->data["gidnumber"];

			if (!$obj_group->update())
			{	
				log_write("debug", "ldap_auth_manage_user", "An error occured whilst attempting to create a group for a new user");
				return 0;
			}
		}


		// success
		return 1;

	} // end of create()



	/*
		create_unique_uid

		Generates a new UID/GID pair and verifies in the database that they are both
		available.

		Returns
		0		Failure
		#		New UID/GID value.
	*/
	function create_unique_id()
	{
		log_debug("ldap_auth_manage_user", "Executing create_unique_id()");
	
		$returnvalue	= 0;
		$uniqueid	= 0;
	

		// fetch the starting ID from the config DB
		$uniqueid	= sql_get_singlevalue("SELECT value FROM config WHERE name='AUTO_INT_UID'");

		if (!$uniqueid)
		{
			log_write("error", "ldap_auth_manager_user", "Unable to fetch seed value from config database");
			return 0;
		}


		// verify in the LDAP database that this UID is not used.
		while ($returnvalue == 0)
		{
			// check group table
			$this->obj_ldap->srvcfg["base_dn"] = "ou=Group,". $GLOBALS["config"]["ldap_dn"];

			if ($this->obj_ldap->search("gidnumber=$uniqueid"))
			{
				// the ID has already been used, try incrementing
				$uniqueid++;
			}
			else
			{
				// check user table
				$this->obj_ldap->srvcfg["base_dn"] = "ou=People,". $GLOBALS["config"]["ldap_dn"];

				if ($this->obj_ldap->search("uidnumber=$uniqueid"))
				{
					// the ID has already been used, try incrementing
					$uniqueid++;
				}
				else
				{
					// found an avaliable ID
					$returnvalue = $uniqueid;
				}
			}
		}

		// update the DB with the new value + 1
		$uniqueid++;
					
		$sql_obj		= New sql_query;
		$sql_obj->string	= "UPDATE config SET value='$uniqueid' WHERE name='AUTO_INT_UID'";
		$sql_obj->execute();


		return $returnvalue;

	} // end of create_unique_uid



	/*
		update

		Update the attributes for the selected user record in LDAP
		
		Dependencies
		Call load_data before executing this function.

		Results
		0	Failure
		1	Success
	*/
	function update()
	{
		log_write("debug", "ldap_auth_manage_user", "Executing update()");


		/*
			Legacy fix for users who were created belonging to the "account" object class and who need to be converted to the inetorgperson
			 class.
			 
			 Here we need to delete the user & re-create them to resolve the limitation of being unable to change object classes, this does
			 work OK, but is not ideal since if there are other attributes not supported by the web interface belonging to the user option,
			 they could potentially be lost
		*/

		if (in_array("account", $this->data["objectclass"]))
		{
			// delete user
			$this->obj_ldap->record_dn	= "uid=". $this->data["uid"] ."";

			if (!$this->obj_ldap->record_delete())
			{
				log_write("debug", "ldap_auth_manage_user", "An error occured whilst attempting to delete user to convert to inetOrgPerson");
				return 0;
			}

			$this->id = NULL;


			// delete user's group
			$obj_group	= New ldap_auth_manage_group;

			$obj_group->data["cn"]			= $this->data["uid"];
			$obj_group->data["gidnumber"]		= $this->data["gidnumber"];

			if (!$obj_group->delete())
			{	
				log_write("debug", "ldap_auth_manage_user", "A non-fatal error occured whilst attempting to delete associated user group");
			}


			log_write("notification","ldap_auth_manage_user", "Performed conversion from \"account\" to \"inetOrgPerson\"");

		}



		// create user if they don't already exist
		if (!$this->id)
		{
			if (!$this->create())
			{
				log_write("error", "ldap_auth_manage_user", "An error occuring whilst attempting to add a new user record to LDAP");

				return 0;
			}
		}

		

		// check if the password has been changed
		if ($this->data["userpassword_plaintext"])
		{
			// hash/format the password in the most appropiate way
			switch ($GLOBALS["config"]["AUTH_USERPASSWORD_TYPE"])
			{
				case "CLEAR_SIMPLE":
					$this->data["userpassword"]	= $this->data["userpassword_plaintext"];
				break;

				case "CLEAR_HEADER":
					$this->data["userpassword"]	= "{clear}". base64_encode($this->data["userpassword_plaintext"]) ."";
				break;

				case "SSHA":	

					// generate 4-byte salt
					$feed	= "0123456789abcdefghijklmnopqrstuvwxyz";
					$salt	= null;

					for ($i=0; $i < 4; $i++)
					{
						$salt .= substr($feed, rand(0, strlen($feed)-1), 1);
					}

					// generate new password
					$this->data["userpassword"]	= "{SSHA}". base64_encode(sha1($this->data["userpassword_plaintext"] . $salt, TRUE) . $salt);

				break;
			}



			

			// remove plaintext
			unset($this->data["userpassword_plaintext"]);
		}


		// set the CN from the name
		$this->data["cn"] = $this->data["gn"] ." ". $this->data["sn"];
	

		// if radius is enabled, add the radius profile schema
		if ($GLOBALS["config"]["FEATURE_RADIUS"] != "disabled")
		{
			// add object class
			$this->data["objectclass"]	= NULL;
			$this->data["objectclass"][]	= "top";
			$this->data["objectclass"][]	= "inetOrgPerson";
			$this->data["objectclass"][]	= "posixAccount";
			$this->data["objectclass"][]	= "shadowAccount";
			$this->data["objectclass"][]	= "radiusprofile";

			// vendor specific: mikrotik
			if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
			{
				$this->data["objectclass"][]	= "radiusMikrotik";
			}
		}


		// set the record to manipulate
		$this->obj_ldap->record_dn	= "uid=". $this->data["uid"] ."";

		// update attributes
		$this->obj_ldap->data = $this->data;

		if ($this->obj_ldap->record_update())
		{
			return 1;
		}

		// failure
		return 0;

	} // end of update()



	/*
		delete

		Deletes the selected user account and it's associated group.
		
		Dependencies
		This function requires the uid to be set, so you may need to run load_data() first.

		Returns
		0	Failure
		1	Success
	*/
	function delete()
	{
		log_write("debug", "ldap_auth_manage_user", "Executing update()");


		/*
			Delete user
		*/
		$this->obj_ldap->record_dn	= "uid=". $this->data["uid"] ."";

		if (!$this->obj_ldap->record_delete())
		{
			log_write("debug", "ldap_auth_manage_user", "An error occured whilst attempting to delete user");
			return 0;
		}



		/*
			Delete matching group, IF both conditions are met

			1. No users belong to the group
			2. Group name is the same as the username
		*/

		$delete			= 1;

		$obj_group		= New ldap_auth_manage_group;
		$obj_group->id		= $this->data["gidnumber"];


		// check group memberships
		$obj_group->load_data();

		foreach ($obj_group->data["memberuid"] as $memberuid)
		{
			if ($memberuid != $this->data["uid"])
			{
				// not safe to delete, another member is assigned to this group
				$delete = 0;
			}
		}


		// check group name == username
		if ($obj_group->data["cn"] != $this->data["uid"])
		{
			$delete = 0;
		}


		// delete the group
		if ($delete)
		{
			if (!$obj_group->delete())
			{	
				log_write("debug", "ldap_auth_manage_user", "An error occured whilst attempting to delete associated user group");
				return 0;
			}
		}
		else
		{
			log_write("debug", "ldap_auth_manager_user", "User's group NOT deleted due to usage by other user accounts or non-standard mapping");
		}



		/*
			Remove user from other groups they might have been assigned to
		*/
	
		// fetch array of all groups user belongs to
		$gidarray = $this->load_data_groups();

		// open each group and remove the user
		foreach ($gidarray as $gid)
		{
			$obj_group	= New ldap_auth_manage_group;
			$obj_group->id	= $gid;

			if ($obj_group->load_data())
			{
				$memberuids			= $obj_group->data["memberuid"];
				$obj_group->data["memberuid"]	= array();

				foreach ($memberuids as $uid)
				{
					// add all uids other than the selected user back
					if ($uid != $this->data["uid"])
					{
						$obj_group->data["memberuid"][] = $uid;
					}
				}
			}

			// update group
			if (!$obj_group->update())
			{
				log_write("debug", "process", "An unexpected error occured whilst attemping to remove user from group ". $obj_group->id);
			}
		}



		// success
		return 1;

	} // end of delete()


} // end of class ldap_auth_manage_user





/*
	
	CLASS LDAP_AUTH_MANAGE_GROUP

	Provides functions for quering, creating, updating and deleting
	authentication groups from an LDAP database.
*/
class ldap_auth_manage_group
{
	var $obj_ldap;		// LDAP object

	var $id;		// ID of the group to handle
	var $data;


	/*
		Constructor
	*/
	function ldap_auth_manage_group()
	{
		/*
			Init LDAP database connection
		*/
		$this->obj_ldap = New ldap_query;

		// connect to LDAP server
		if (!$this->obj_ldap->connect())
		{
			log_write("error", "user_auth", "An error occurred in the authentication backend, please contact your system administrator");
			return -1;
		}

		// set base_dn to run Group lookups in
		$this->obj_ldap->srvcfg["base_dn"] = "ou=Group,". $GLOBALS["config"]["ldap_dn"];
	}



	/*
		list_groups

		Fetches a list of groups with gidnumber and groupname information, saves into the $this->data array.

		Returns
		0	Failure to query LDAP
		1	Success
	*/
	function list_groups()
	{
		log_debug("ldap_auth_manage_group", "Executing list_groups()");


		// fetch all groups
		if ($this->obj_ldap->search("gidnumber=*", array("gidnumber", "cn")))
		{
			log_debug("ldap_auth_manager_group", "Found a total of ". $this->obj_ldap->data_num_rows ." groups");

			$this->data = array();

			for ($i=0; $i < $this->obj_ldap->data_num_rows; $i++)
			{
				// set values
				$this->data[$i]["gidnumber"]	= $this->obj_ldap->data[$i]["gidnumber"][0];
				$this->data[$i]["cn"]		= $this->obj_ldap->data[$i]["cn"][0];
			}

			return 1;
		}

		return 0;

	}




	/*
		verify_id

		Checks that the provided ID is a valid group

		Results
		0	Failure to find the ID
		1	Success - group exists
	*/

	function verify_id()
	{
		log_debug("ldap_auth_manage_group", "Executing verify_id()");

		if ($this->id)
		{
			// run query against group
			$this->obj_ldap->search("gidnumber=". $this->id, array("gidnumber"));

			if ($this->obj_ldap->data_num_rows)
			{
				return 1;
			}
			else
			{
				log_write("debug", "page", "Invalid group ". $this->id ." requested");
			}
		}

		return 0;

	} // end of verify_id



	/*
		verify_groupname

		Checks that the provided group name (cn) exists and returns the ID that it
		belongs to.

		Results
		0	Failure to find the ID
		#	Group ID
	*/

	function verify_groupname($groupname)
	{
		log_debug("ldap_auth_manage_group", "Executing verify_groupname($groupname)");

		// run query against groups
		$this->obj_ldap->search("cn=". $groupname, array("gidnumber"));

		if ($this->obj_ldap->data_num_rows)
		{
			$this->id = $this->obj_ldap->data[0]["gidnumber"][0];

			return $this->id;
		}
		else
		{
			log_write("debug", "page", "Invalid group ". $this->id ." requested");
		}

		return 0;

	} // end of verify_groupname



	/*
		load_data

		Load all the information regarding the selected group.

		Results
		0	Failure
		1	Success
	*/
	function load_data()
	{
		log_debug("ldap_auth_manage_group", "Executing load_data()");

		// fetch all group attributes
		$this->obj_ldap->search("gidnumber=". $this->id);

		if ($this->obj_ldap->data_num_rows)
		{
			// get values
			$this->data["cn"]		= $this->obj_ldap->data[0]["cn"][0];
			$this->data["gidnumber"]	= $this->obj_ldap->data[0]["gidnumber"][0];

			// get members
			for ($i=0; $i < $this->obj_ldap->data[0]["memberuid"]["count"]; $i++)
			{
				$this->data["memberuid"][$i] = $this->obj_ldap->data[0]["memberuid"][$i];
			}	

			// set radius values
			if (sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS' LIMIT 1") != "disabled")
			{
				// standard attributes
				$radius_attributes = radius_attr_standard();

				foreach ($radius_attributes as $attribute)
				{
					if (!empty($this->obj_ldap->data[0][ strtolower($attribute) ][0]))
					{
						$this->data[ $attribute ]		= $this->obj_ldap->data[0][ strtolower($attribute) ][0];
					}
				}


				// vendor specific: mikrotik
				if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
				{
					$radius_attributes = array_keys(radius_attr_mikrotik());

					foreach ($radius_attributes as $attribute)
					{
						if (!empty($this->obj_ldap->data[0][ strtolower($attribute) ][0]))
						{
							$this->data[ $attribute ]	= $this->obj_ldap->data[0][ strtolower($attribute) ][0];
						}
					}
				}


				// vendor attributes
				$num_vendor_fields = sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS_MAXVENDOR'");

				for ($i=0; $i < $num_vendor_fields; $i++)
				{
					if (!empty($this->obj_ldap->data[0]["radiuscheckitem"][$i]))
					{
						$this->data["radiusCheckItem"][$i]	= $this->obj_ldap->data[0]["radiuscheckitem"][$i];
					}

					if (!empty($this->obj_ldap->data[0]["radiusreplyitem"][$i]))
					{
						$this->data["radiusReplyItem"][$i]	= $this->obj_ldap->data[0]["radiusreplyitem"][$i];
					}
				}

			}


			return 1;
		}

		return 0;
	}


	/*
		create

		Creates a new LDAP group. This function is typically called automatically by the update() function.

		Results
		0	Failure
		#	Success - returns ID of group
	*/
	function create()
	{
		log_write("debug", "ldap_auth_manage_group", "Executing create()");


		/*
			Generate GID (if needed)
		*/

		if (!$this->data["gidnumber"])
		{
			$this->id			= $this->create_unique_id();
			$this->data["gidnumber"]	= $this->id;
		}


		/*
			Create new LDAP group object
		*/
		
		// set objectclasses
		$this->data["objectclass"][]	= "top";
		$this->data["objectclass"][]	= "posixGroup";

		// set DN
		$this->obj_ldap->record_dn = "cn=". $this->data["cn"];

		// create record
		$this->obj_ldap->data = $this->data;

		if (!$this->obj_ldap->record_create())
		{
			return 0;
		}


		// success
		return 1;

	} // end of create()



	/*
		create_unique_uid

		Generates a new GID pair and verifies in the database that it is available.

		Returns
		0		Failure
		#		New GID value.
	*/
	function create_unique_id()
	{
		log_debug("ldap_auth_manage_group", "Executing create_unique_id()");
	
		$returnvalue	= 0;
		$uniqueid	= 0;
	

		// fetch the starting ID from the config DB
		$uniqueid	= sql_get_singlevalue("SELECT value FROM config WHERE name='AUTO_INT_GID'");

		if (!$uniqueid)
		{
			log_write("error", "ldap_auth_manage_group", "Unable to fetch seed value from config database");
			return 0;
		}


		// verify in the LDAP database that this UID is not used.
		while ($returnvalue == 0)
		{
			// check group table
			$this->obj_ldap->srvcfg["base_dn"] = "ou=Group,". $GLOBALS["config"]["ldap_dn"];

			if ($this->obj_ldap->search("gidnumber=$uniqueid"))
			{
				// the ID has already been used, try incrementing
				$uniqueid++;
			}
			else
			{
				// found an avaliable ID
				$returnvalue = $uniqueid;
			}
		}

		// update the DB with the new value + 1
		$uniqueid++;
					
		$sql_obj		= New sql_query;
		$sql_obj->string	= "UPDATE config SET value='$uniqueid' WHERE name='AUTO_INT_GID'";
		$sql_obj->execute();


		return $returnvalue;

	} // end of create_unique_id



	/*
		update

		Update the attributes for the selected group record in LDAP
		
		Dependencies
		Call load_data before executing this function.

		Results
		0	Failure
		1	Success
	*/
	function update()
	{
		log_write("debug", "ldap_auth_manage_group", "Executing update()");

		// create group if they don't already exist
		if (!$this->id)
		{
			if (!$this->create())
			{
				log_write("error", "ldap_auth_manage_group", "An error occuring whilst attempting to add a new group record to LDAP");

				return 0;
			}
		}

		// if radius is enabled, add the radius profile schema
		if ($GLOBALS["config"]["FEATURE_RADIUS"] != "disabled")
		{
			// add object class
			$this->data["objectclass"]	= NULL;
			$this->data["objectclass"][]	= "top";
			$this->data["objectclass"][]	= "posixGroup";
			$this->data["objectclass"][]	= "radiusprofile";

			// vendor specific: mikrotik
			if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
			{
				$this->data["objectclass"][]	= "radiusMikrotik";
			}

		}


		// set the record to manipulate
		$this->obj_ldap->record_dn	= "cn=". $this->data["cn"] ."";

		// update attributes
		$this->obj_ldap->data = $this->data;

		if ($this->obj_ldap->record_update())
		{
			return 1;
		}

		// failure
		return 0;

	} // end of update()



	/*
		delete

		Deletes the selected group.
		
		Dependencies
		This function requires the cn to be set, so you may need to run load_data() first.

		Returns
		0	Failure
		1	Success
	*/
	function delete()
	{
		log_write("debug", "ldap_auth_manage_group", "Executing delete()");

		// set the record to manipulate
		$this->obj_ldap->record_dn	= "cn=". $this->data["cn"] ."";

		// delete
		if ($this->obj_ldap->record_delete())
		{
			return 1;
		}

		// failure
		return 0;

	} // end of delete()

} // end of class ldap_auth_manage_group



?>
