<?php
/*
	LDAPAUTHMANAGER SOAP API

	XML-based API for pushing log information to LDAPAuthManager.
*/


// include libraries
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


class api_ldapauthmanager
{
	var $auth_server;		// ID of the ldap server that has authenticated.
	var $auth_online;		// set to 1 if authenticated


	/*
		constructor
	*/
	function api_ldapauthmanager()
	{
		$this->auth_server	= $_SESSION["auth_server"];
		$this->auth_online	= $_SESSION["auth_online"];
	}



	/*
		authenticate

		Authenticates a SOAP client call using the SOAP_API_KEY configuration option to enable/prevent access

		Returns
		0	Failure
		#	ID of the LDAP server user has authenticated with
	*/
	function authenticate($server_name, $api_auth_key)
	{
		log_write("debug", "api_ldapauthmanager", "Executing authenticate($server_name, $api_auth_key)");

		// sanitise input
		$server_name	= @security_script_input_predefined("any", $server_name);
		$api_auth_key	= @security_script_input_predefined("any", $api_auth_key);

		if (!$server_name || $server_name == "error" || !$api_auth_key || $api_auth_key == "error")
		{
			throw new SoapFault("Sender", "INVALID_INPUT");
		}


		// verify input
		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT id FROM ldap_servers WHERE server_name='$server_name' AND api_auth_key='$api_auth_key' LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			$sql_obj->fetch_array();

			$this->auth_online		= 1;
			$this->auth_server		= $sql_obj->data[0]["id"];

			$_SESSION["auth_online"]	= $this->auth_online;
			$_SESSION["auth_server"]	= $this->auth_server;

			return $this->auth_server;
		}
		else
		{
			throw new SoapFault("Sender", "INVALID_ID");
		}

	} // end of authenticate




	/*
		log_write

		Writes a new log value to the database

		Fields
		log_timestamp		UNIX timestamp
		log_type		Category (max 10 char)
		log_contents		Contents of log message
	*/

	function log_write($log_timestamp, $log_type, $log_contents)
	{
		log_write("debug", "api_ldapauthmanager", "Executing log_write ($log_timestamp, $log_type, $log_contents");

		// refuse authentication if logging disabled
		if (!$GLOBALS["config"]["FEATURE_LOGS_ENABLE"])
		{
			throw new SoapFault("Sender", "FEATURE_DISABLED");
		}

		// sanitise input
		$log_timestamp	= @security_script_input_predefined("int", $log_timestamp);
		$log_type	= @security_script_input_predefined("any", $log_type);
		$log_contents	= @security_script_input_predefined("any", $log_contents);

		if (!$log_timestamp || $log_timestamp == "error" || !$log_type || $log_type == "error" || !$log_contents || $log_contents == "error")
		{
			throw new SoapFault("Sender", "INVALID_INPUT");
		}


		if ($this->auth_online)
		{
			// write log
			$obj_log 		= New ldap_logs;
			$obj_log->id_server	= $this->auth_server;
			$obj_log->log_push($log_timestamp, $log_type, $log_contents);
		}
		else
		{
			throw new SoapFault("Sender", "ACCESS_DENIED");
		}

	} // end of log_write

				
} // end of api_ldapauthmanager class



// define server
$server = new SoapServer("ldapauthmanager.wsdl");
$server->setClass("api_ldapauthmanager");
$server->handle();


?>

