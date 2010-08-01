<?php
/*
	include/application/inc_servers.php

	Functions/classes for managing and querying LDAP servers which
	are being managed by LDAPAuthManager.
*/




/*
	CLASS LDAP_SERVER

	Functions for managing and quering servers.
*/
class ldap_server
{
	var $id;		// ID of the server to manipulate (if any)
	var $data;



	/*
		verify_id

		Checks that the provided ID is a valid ldap server.

		Results
		0	Failure to find the ID
		1	Success - server exists
	*/

	function verify_id()
	{
		log_debug("ldap_server", "Executing verify_id()");

		if ($this->id)
		{
			$sql_obj		= New sql_query;
			$sql_obj->string	= "SELECT id FROM `ldap_servers` WHERE id='". $this->id ."' LIMIT 1";
			$sql_obj->execute();

			if ($sql_obj->num_rows())
			{
				return 1;
			}
		}

		return 0;

	} // end of verify_id



	/*
		verify_server_name

		Checks that the server name supplied has not already been taken.

		Results
		0	Failure - name in use
		1	Success - name is available
	*/

	function verify_server_name()
	{
		log_debug("ldap_server", "Executing verify_server_name()");

		$sql_obj			= New sql_query;
		$sql_obj->string		= "SELECT id FROM `ldap_servers` WHERE server_name='". $this->data["server_name"] ."' ";

		if ($this->id)
			$sql_obj->string	.= " AND id!='". $this->id ."'";

		$sql_obj->string		.= " LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}
		
		return 1;

	} // end of verify_server_name



	/*
		load_data

		Load the ldap server's information into the $this->data array.

		Returns
		0	failure
		1	success
	*/
	function load_data()
	{
		log_debug("ldap_server", "Executing load_data()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT * FROM ldap_servers WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			$sql_obj->fetch_array();


			// set attributes
			$this->data = $sql_obj->data[0];

			/*
			// fetch sync statuses
			if (sql_get_singlevalue("SELECT value FROM config WHERE name='SYNC_STATUS_CONFIG'") != $sql_obj->data[0]["api_sync_config"])
			{
				// out of sync, set to date
				$this->data["sync_status_config"]	= $sql_obj->data[0]["api_sync_config"];
			}
			*/

			if ((time() - $sql_obj->data[0]["api_sync_log"]) > 86400)
			{
				// logging hasn't happened for at least 24 hours, flag logging as failed
				$this->data["sync_status_log"]		= $sql_obj->data[0]["api_sync_log"];
			}


			return 1;
		}

		// failure
		return 0;

	} // end of load_data




	/*
		action_create

		Create a new ldap server based on the data in $this->data

		Results
		0	Failure
		#	Success - return ID
	*/
	function action_create()
	{
		log_debug("ldap_server", "Executing action_create()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "INSERT INTO `ldap_servers` (server_name, api_sync_log) VALUES ('". $this->data["server_name"] ."', '1')";
		$sql_obj->execute();

		$this->id = $sql_obj->fetch_insert_id();

		return $this->id;

	} // end of action_create




	/*
		action_update

		Update a ldap server's details based on the data in $this->data. If no ID is provided,
		it will first call the action_create function.

		Returns
		0	failure
		#	success - returns the ID
	*/
	function action_update()
	{
		log_debug("ldap_server", "Executing action_update()");


		/*
			Start Transaction
		*/
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();


		/*
			If no ID supplied, create a new ldap server first
		*/
		if (!$this->id)
		{
			$mode = "create";

			if (!$this->action_create())
			{
				return 0;
			}
		}
		else
		{
			$mode = "update";
		}



		/*
			Update ldap server details
		*/

		$sql_obj->string	= "UPDATE `ldap_servers` SET "
						."server_name='". $this->data["server_name"] ."', "
						."server_description='". $this->data["server_description"] ."', "
						."api_auth_key='". $this->data["api_auth_key"] ."' "
						."WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		


		/*
			Commit
		*/

		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "ldap_server", "An error occured when updating the ldap server.");

			return 0;
		}
		else
		{
			$sql_obj->trans_commit();

			if ($mode == "update")
			{
				log_write("notification", "ldap_server", "LDAP server has been successfully updated.");
			}
			else
			{
				log_write("notification", "ldap_server", "LDAP server successfully created.");
			}
			
			return $this->id;
		}

	} // end of action_update



	/*
		action_update_log_version

		Update the version of the last log sync in the database

		Values
		version		timestamp version to use

		Returns
		0		failure
		1		success
	*/
	function action_update_log_version($version)
	{
		log_debug("ldap_server", "Executing action_update_log_version($version)");


		/*
			Start Transaction
		*/
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();


		/*
			Update configuration version
		*/

		$sql_obj->string	= "UPDATE `ldap_servers` SET api_sync_log='$version' WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();


		/*
			Commit
		*/

		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "ldap_server", "An error occured when updating the ldap server.");

			return 0;
		}
		else
		{
			$sql_obj->trans_commit();

			log_write("notification", "ldap_server", "LDAP server version has been successfully updated.");

			return 1;
		}

	} // end of action_update_log_version





	/*
		action_delete

		Deletes a ldap server

		Results
		0	failure
		1	success
	*/
	function action_delete()
	{
		log_debug("ldap_server", "Executing action_delete()");

		/*
			Start Transaction
		*/

		$sql_obj = New sql_query;
		$sql_obj->trans_begin();


		/*
			Delete LDAP Server
		*/
			
		$sql_obj->string	= "DELETE FROM ldap_servers WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();


		/*
			Un-associated any matched log entries
		*/

		$sql_obj->string	= "UPDATE logs SET id_server='0' WHERE id_server='". $this->id ."'";
		$sql_obj->execute();


		/*
			Commit
		*/
		
		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "ldap_server", "An error occured whilst trying to delete the ldap server.");

			return 0;
		}
		else
		{
			$sql_obj->trans_commit();

			log_write("notification", "ldap_server", "LDAP server has been successfully deleted.");

			return 1;
		}
	}


} // end of class:ldap_server



?>
