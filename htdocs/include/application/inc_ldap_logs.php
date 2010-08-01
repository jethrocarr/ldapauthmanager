<?php
/*
	inc_ldap_logs.php

	Provides functions for handling LDAP server logs.
*/




/*
	CLASS LDAP_LOGS

	Functions for quering and updating logs.
*/
class ldap_logs
{
	var $id_server;			// ID of server

	var $query_data;		// returned log rows
	var $query_recordlimit;		// maximum number of log rows to return
	var $query_filter_id_server;	// filter to this server
	var $query_filter_string;	// filter to this search string



	/*
		log_push

		Creates a new log entry based on the supplied information.

		Results
		0	Failure
		1	Success
	*/
	function log_push($log_timestamp, $log_type, $log_contents)
	{
		log_debug("ldap_logs", "Executing log_push($log_timestamp, $log_type, $log_contents)");

		// write log
		$sql_obj		= New sql_query;
		$sql_obj->string	= "INSERT INTO logs (id_server, timestamp, log_type, log_contents) VALUES ('". $this->id_server ."', '$log_timestamp', '$log_type', '$log_contents')";
		
		if (!$sql_obj->execute())
		{
			return 0;
		}


		// update last sync on ldap server option
		if ($this->id_server)
		{
			$obj_server		= New ldap_server;
			$obj_server->id		= $this->id_server;
			$obj_server->action_update_log_version($log_timestamp);
		}


		return 1;

	} // end of log_push



} // end of class: ldap_logs

?>
