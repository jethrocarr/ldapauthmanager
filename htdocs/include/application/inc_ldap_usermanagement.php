<?php
/*
	inc_ldap_usermanagement.php

	Provides high-level functions for managing users and groups in an LDAP database.
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
				log_write("error", "page", "Invalid user ". $this->id ." requested");
			}
		}

		return 0;

	} // end of verify_id




	/*
		load_data

		Load all the information regarding the selected user.

		Results
		0	Failure
		1	Success
	*/
	function load_data()
	{
		log_debug("ldap_auth_manage_user", "Executing verify_id()");

		// fetch all user attributes
		$this->obj_ldap->search("uidnumber=". $this->id);

		if ($this->obj_ldap->data_num_rows)
		{
			// set values
			$this->data["uid"]		= $this->obj_ldap->data[0]["uid"][0];
			$this->data["cn"]		= $this->obj_ldap->data[0]["cn"][0];
			$this->data["uidnumber"]	= $this->obj_ldap->data[0]["uidnumber"][0];
			$this->data["gidnumber"]	= $this->obj_ldap->data[0]["gidnumber"][0];
			$this->data["loginshell"]	= $this->obj_ldap->data[0]["loginshell"][0];
			$this->data["homedirectory"]	= $this->obj_ldap->data[0]["homedirectory"][0];
			$this->data["userpassword"]	= $this->obj_ldap->data[0]["userpassword"][0];

	

			return 1;
		}

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
			$this->id			 = $this->create_unique_id();

			$this->data["uidnumber"]	= $this->id;
			$this->data["gidnumber"]	= $this->id;
		}


		/*
			Create new LDAP user object
		*/
		
		// set objectclasses
		$this->data["objectclass"][]	= "top";
		$this->data["objectclass"][]	= "account";
		$this->data["objectclass"][]	= "posixAccount";
		$this->data["objectclass"][]	= "shadowAccount";

		// password placeholder
		$this->data["userpassword"]	= "{SSHA}x";

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
			Create new LDAP group object
		*/

		$obj_group	= New ldap_auth_manage_group;

		$obj_group->data["cn"]			= $this->data["uid"];
		$obj_group->data["gidnumber"]		= $this->data["gidnumber"];

		if (!$obj_group->update())
		{	
			log_write("debug", "ldap_auth_manage_user", "An error occured whilst attempting to create a group for a new user");
			return 0;
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
			die("Unable to fetch seed value from config database");


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
			// generate 4-byte salt
			$feed	= "0123456789abcdefghijklmnopqrstuvwxyz";
			$salt	= null;

			for ($i=0; $i < 4; $i++)
			{
				$salt .= substr($feed, rand(0, strlen($feed)-1), 1);
			}

			// generate new password
			$this->data["userpassword"] = "{SSHA}". base64_encode(sha1($this->data["userpassword_plaintext"] . $salt, TRUE) . $salt);

			// remove plaintext
			unset($this->data["userpassword_plaintext"]);
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

		// set the record to manipulate
		$this->obj_ldap->record_dn	= "uid=". $this->data["uid"] ."";

		// delete
		if ($this->obj_ldap->record_delete())
		{
			return 1;
		}


		// TODO: delete group
	

		// failure
		return 0;

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
				log_write("error", "page", "Invalid group ". $this->id ." requested");
			}
		}

		return 0;

	} // end of verify_id




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
			die("Unable to fetch seed value from config database");


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
		This function requires the uid to be set, so you may need to run load_data() first.

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
