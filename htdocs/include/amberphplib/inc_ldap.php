<?php
/*
	inc_ldap.php

	Provides high-level functions for handling and working with LDAP databases.
	
	These functions are typically used by the user_auth class for handling user authentication, however
	the functions can be used for many different LDAP manipulation needs.

	Because of the nature of LDAP, we don't need to abstract basic calls like we do with SQL in order
	to handle different databases, so only use the standard php LDAP functions if needed otherwise.
*/



class ldap_query
{
	var $ldapcon;			// reference to LDAP database session - if unset
					// will default to last LDAP database opened.

	var $srvcfg;			// settings for server to connect to.

	var $record_dn;			// DN for a specific entry

	var $data;			// used to store queried entries
	var $data_num_rows;		// used to store num of entries


	/*
		connect()

		Initates a connection to the LDAP server and binds with the
		configured user.

		TODO: check out SSL support for when accessing hosts via the network

		Returns
		0	Failure
		1	Success
	*/
	function connect()
	{
		log_debug("ldap_query", "Executing connect()");


		// select default configuration if none has been provided
		if (!isset($this->srvcfg))
		{
			// use config files for LDAP server settings.
			if ($GLOBALS["config"]["ldap_host"])
			{
				$this->srvcfg["host"]		= $GLOBALS["config"]["ldap_host"];
				$this->srvcfg["port"]		= $GLOBALS["config"]["ldap_port"];
				$this->srvcfg["base_dn"]	= $GLOBALS["config"]["ldap_dn"];
				$this->srvcfg["user"]		= $GLOBALS["config"]["ldap_manager_user"];
				$this->srvcfg["password"]	= $GLOBALS["config"]["ldap_manager_pwd"];
			}
		}


		// connect to server
		$this->ldapcon = ldap_connect($this->srvcfg["host"], $this->srvcfg["port"]);

		if (!$this->ldapcon)
		{
			log_debug("ldap_query", "Unable to connect to LDAP server ". $this->srvcfg["host"] ." on port ". $this->srvcfg["port"] ."");
			return 0;
		}


		// bind user
		if (ldap_bind($this->ldapcon, $this->srvcfg["user"], $this->srvcfg["password"]))
		{
			log_debug("ldap_query", "Successfully connect to LDAP database on ". $this->srvcfg["host"] ." as ". $this->srvcfg["user"] ."");
			return 1;
		}
		else
		{
			log_debug("ldap_query", "Unable to connect to LDAP database on ". $this->srvcfg["host"] ." as ". $this->srvcfg["user"] ."");
			return 0;
		}


	} // end of connect()



	/*
		disconnect()

		Disconnects from the currently active LDAP server.

		Returns
		0	Failure
		1	Success
	*/

	function disconnect()
	{
		log_debug("ldap_query", "Executing disconnect()");

		// disconnect from server.
		ldap_unbind($this->ldapcon);

		return 1;

	} // end of disconnect()



	/*
		search

		Search the configured base_dn with the provided filter and returns number of matching entries as well
		as storing the data in $this->data array for easy access.

		Fields
		filter		The search filter can be simple or advanced, using boolean operators in the format described in the LDAP documentation
		attributes	(optional) array of what attributes to return

		Returns
		0		Failure
		#		Number of entries returned
	*/
	function search($filter, $attributes = array())
	{
		log_debug("ldap_query", "Executing search($filter, \$attribute_array)");

		$sr_link	= ldap_search($this->ldapcon, $this->srvcfg["base_dn"], $filter, $attributes);
		$this->data	= ldap_get_entries($this->ldapcon, $sr_link);

		if (!count($this->data))
		{
			log_debug("ldap_query", "Unable to match any entries in LDAP database");
			return 0;
		}
		else
		{
			// set the number of rows
			$this->data_num_rows =	$this->data["count"];

			return $this->data_num_rows;
		}

		return 0;

	}



	/*
		entry_load_data

		Fetches all the data for a *single* entry as per $this->entry_dn

		Returns
		0	Failure
		1	Success
	*/
/*
	function entry_load_data()
	{
		log_write("debug", "ldap_query", "Executing entry_load()");

		if ($this->search($this->entry_dn))
		{
			$data = $this-> 
		}

		return 0;

	}
*/

	/*
		record_update

		Updates an existing LDAP entry with new data. Best way to use this is to run entry_load() to get all
		current attributes and then update the $this->data array.

		NOTE: this function will not add a new entry


		Fields
		$this->record_dn	DN of the record to modify
		$this->data		Array of attributes to write (see php ldap_add for syntax information)

		Returns
		0		Failure
		1		Success
	*/

	function record_update()
	{
		log_write("debug", "ldap_query", "Executing record_update()");

		if (ldap_modify($this->ldapcon, $this->record_dn .",". $this->srvcfg["base_dn"], $this->data))
		{
			return 1;
		}

		return 0;
	}



		

}





?>
