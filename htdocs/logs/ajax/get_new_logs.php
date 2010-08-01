<?php 
/*
	logs/ajax/get_new_logs

	access:
		ldapadmins

	Returns new log rows for LDAPAuthManager's log monitoring interface.
*/


require("../../include/config.php");
require("../../include/amberphplib/main.php");
require("../../include/application/main.php");


if (user_permissions_get("ldapadmins"))
{
	// get values
	$highest_id		= @security_script_input_predefined("int", $_GET['highest_id']);
	

	// fetch updated rows from databases
	$sql_obj		= New sql_query;
	$sql_obj->string	= "SELECT
					logs.id as id,
					timestamp,
					ldap_servers.server_name as server_name,
					log_type,
					log_contents
					FROM logs
					LEFT JOIN ldap_servers ON ldap_servers.id = logs.id_server
					WHERE logs.id > ". $highest_id ."
					ORDER BY timestamp DESC";
	$sql_obj->execute();
	

	$data		= array();
	$new_highest_id	= $highest_id;	


	if ($sql_obj->num_rows())
	{
		$sql_obj->fetch_array();

		foreach ($sql_obj->data as $data_row)
		{
			$id				= $data_row["id"];

			$data[$id]["timestamp"]		= time_format_humandate(date("Y-m-d", $data_row["timestamp"]))." ".date("H:i:s", $data_row["timestamp"]);
			$data[$id]["server_name"]	= $data_row["server_name"];
			$data[$id]["log_type"]		= $data_row["log_type"];
			$data[$id]["log_contents"]	= $data_row["log_contents"];
			
			if ($id > $new_highest_id)
			{
				$new_highest_id		= $id;
				$data["new_highest_id"]	= $new_highest_id;
			}
		}
	}

	print json_encode($data);


	// clear debug logging, otherwise will end up with far too much debug log
	$_SESSION["user"]["log_debug"] = array();
}
else
{
	// access denied
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
