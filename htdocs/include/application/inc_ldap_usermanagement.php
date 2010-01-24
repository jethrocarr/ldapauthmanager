<?php
/*
	inc_ldap_usermanagement.php

	Provides high-level functions for managing users and groups in an LDAP database.
*/


class ldap_auth_info extends ldap_query
{
	function user_list()
	{
	}

	function group_list()
	{
	}
}


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
		1	Success - customer exists
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

			// set objectclasses
			//$this->data["objectclass"][]	= "top";
			//$this->data["objectclass"][]	= "account";
			//$this->data["objectclass"][]	= "posixAccount";
			//$this->data["objectclass"][]	= "shadowAccount";


			return 1;
		}

		return 0;
	}


	function user_create()
	{
	}


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

		die("failure");
		return 0;
	}


	function user_delete()
	{
	}

	function user_group_change()
	{
	}


	function group_data()
	{
	}

	function group_create()
	{
	}

	function group_update()
	{
	}

	function group_delete()
	{
	}
}



?>
