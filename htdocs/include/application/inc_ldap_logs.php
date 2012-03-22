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

		// do retention clean check
		if ($GLOBALS["config"]["LOG_RETENTION_PERIOD"])
		{
			// check when we last ran a retention clean
			if ($GLOBALS["config"]["LOG_RETENTION_CHECKTIME"] < (time() - 86400))
			{
				$this->log_retention_clean();
			}
		}

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


	/*
		log_retention_clean

		Cleans the log table of outdated records.

		This process needs to take place at least every day to ensure speedy performance and is triggered from either
		a log API call or an audit log entry (since there is no guarantee that either logging method is going to be enabled,
		we have to trigger on any.)

		Returns
		0	No log clean requires
		1	Performed log clean.
	*/

	function log_retention_clean()
	{
		log_write("debug", "ldap_logs", "Executing log_retention_clean()");
		log_write("debug", "ldap_logs", "A retention clean is required - last one was more than 24 hours ago.");

		// calc date to clean up to
		$clean_time	= time() - ($GLOBALS["config"]["LOG_RETENTION_PERIOD"] * 86400);
		$clean_date	= time_format_humandate($clean_time);


		// clean
		$obj_sql_clean		= New sql_query;
		$obj_sql_clean->string	= "DELETE FROM logs WHERE timestamp <= '$clean_time'";
		$obj_sql_clean->execute();

		$clean_removed = $obj_sql_clean->fetch_affected_rows();

		unset($obj_sql_clean);


		// update rentention time check
		$obj_sql_clean		= New sql_query;
		$obj_sql_clean->string	= "UPDATE `config` SET value='". time() ."' WHERE name='LOG_RETENTION_CHECKTIME' LIMIT 1";
		$obj_sql_clean->execute();

		unset($obj_sql_clean);


		// add audit entry - we have to set the LOG_RETENTION_CHECKTIME variable here to avoid
		// looping the program, as the SQL change above won't be applied until the current transaction
		// is commited.

		$GLOBALS["config"]["LOG_RETENTION_CHECKTIME"] = time();
		$this->log_push(time(), "audit", "Automated log retention clean completed, removed $clean_removed records order than $clean_date");


		// complete
		log_write("debug", "ldap_logs", "Completed retention log clean, removed $clean_removed log records older than $clean_date");

		return 1;
	}



} // end of class: ldap_logs

?>
