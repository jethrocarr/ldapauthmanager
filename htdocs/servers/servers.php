<?php
/*
	servers/servers.php

	access:
		ldapadmins

	Management page for LDAP servers - this page allows servers to be added or removed,
	API configuration and accessing serer logs.
*/


class page_output
{
	var $obj_table;


	function check_permissions()
	{
		return user_permissions_get("ldapadmins");
	}

	function check_requirements()
	{
		// nothing todo
		return 1;
	}


	function execute()
	{
		// establish a new table object
		$this->obj_table = New table;

		$this->obj_table->language	= $_SESSION["user"]["lang"];
		$this->obj_table->tablename	= "ldap_server";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "server_name", "");
		$this->obj_table->add_column("standard", "server_description", "");
		$this->obj_table->add_column("standard", "sync_log_status", "NONE");

		// defaults
		$this->obj_table->columns		= array("server_name", "server_description", "sync_log_status");
		$this->obj_table->columns_order		= array("server_name");
		$this->obj_table->columns_order_options	= array("server_name");

		$this->obj_table->sql_obj->prepare_sql_settable("ldap_servers");
		$this->obj_table->sql_obj->prepare_sql_addfield("id", "");
		$this->obj_table->sql_obj->prepare_sql_addfield("api_sync_log", "");

		// load data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();


		// report sync status
		for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
		{
			// check logging
			if ($this->obj_table->data[$i]["api_sync_log"] < (mktime() - 43200))
			{
				$this->obj_table->data[$i]["sync_log_status"]	= "<span class=\"table_highlight_important\">". lang_trans("status_log_unsynced") ."</span>";
			}
			else
			{
				$this->obj_table->data[$i]["sync_log_status"]	= "<span class=\"table_highlight_open\">". lang_trans("status_log_synced") ."</span>";
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>LDAP SERVERS</h3>";
		print "<p>If you want to use LDAPAuthManager for recording all the LDAP server log messages in a single place, you can use this page to setup the LDAP servers and API keys for connecting to LDAPAuthManager for submitting logs.</p>";

		// table data
		if (!$this->obj_table->data_num_rows)
		{
			format_msgbox("important", "<p>There are currently no LDAP servers configured for monitoring.</p>");
		}
		else
		{
			// details link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_details", "servers/view.php", $structure);

			// logging link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_logs", "servers/logs.php", $structure);

			// delete link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_delete", "servers/delete.php", $structure);


			// display the table
			$this->obj_table->render_table_html();

		}

		// add link
		print "<p><a class=\"button\" href=\"index.php?page=servers/add.php\">Add New Server</a></p>";

	}

}


?>
