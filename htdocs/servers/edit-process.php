<?php
/*
	servers/edit-process.php

	access:
		ldapadmins

	Updates or creates a new ldap server entry.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('ldapadmins'))
{
	/*
		Form Input
	*/

	$obj_ldap_server		= New ldap_server;
	$obj_ldap_server->id		= security_form_input_predefined("int", "id_ldap_server", 0, "");


	// are we editing an existing server or adding a new one?
	if ($obj_ldap_server->id)
	{
		if (!$obj_ldap_server->verify_id())
		{
			log_write("error", "process", "The ldap server you have attempted to edit - ". $obj_ldap_server->id ." - does not exist in this system.");
		}
		else
		{
			// load existing data
			$obj_ldap_server->load_data();
		}
	}

	// basic fields
	$obj_ldap_server->data["server_name"]			= security_form_input_predefined("any", "server_name", 1, "");
	$obj_ldap_server->data["server_description"]		= security_form_input_predefined("any", "server_description", 0, "");
	$obj_ldap_server->data["api_auth_key"]			= security_form_input_predefined("any", "api_auth_key", 1, "");




	/*
		Verify Data
	*/

	// ensure the server name is unique
	if (!$obj_ldap_server->verify_server_name())
	{
		log_write("error", "process", "The requested server name already exists, have you checked that the server you're trying to add doesn't already exist?");

		error_flag_field("server_name");
	}


	/*
		Process Data
	*/

	if (error_check())
	{
		if ($obj_ldap_server->id)
		{
			$_SESSION["error"]["form"]["ldap_server_edit"]	= "failed";
			header("Location: ../index.php?page=servers/view.php&id=". $obj_ldap_server->id ."");
		}
		else
		{
			$_SESSION["error"]["form"]["ldap_server_edit"]	= "failed";
			header("Location: ../index.php?page=servers/add.php");
		}

		exit(0);
	}
	else
	{
		// clear error data
		error_clear();


		/*
			Update ldap server
		*/

		$obj_ldap_server->action_update();


		/*
			Return
		*/

		header("Location: ../index.php?page=servers/view.php&id=". $obj_ldap_server->id ."");
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
