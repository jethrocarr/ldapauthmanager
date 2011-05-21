<?php
/*
	group_management/groups.php

	access:
		ldapadmins

	Administrator-only tool for configuring LDAP groups
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
		$this->obj_table->tablename	= "group_list";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "groupname", "cn");
		$this->obj_table->add_column("standard", "gidnumber", "gidnumber");
		$this->obj_table->add_column("standard", "memberuid", "memberuid");

		// defaults
		$this->obj_table->columns		= array("groupname", "gidnumber", "memberuid");
//		$this->obj_table->columns_order		= array("groupname");
//		$this->obj_table->columns_order_options	= array("groupname", "gidnumber", "memberuid");

		// acceptable filter options
		$structure = NULL;
		$structure["fieldname"] = "searchbox";
		$structure["type"]	= "input";
		$structure["ldap"]	= "(|(cn=value)(gidnumber=value))";
		$this->obj_table->add_filter($structure);

		$structure = NULL;
		$structure["fieldname"] 	= "hide_user_group_maps";
		$structure["type"]		= "checkbox";
		$structure["defaultvalue"]	= "on";
		$structure["options"]["label"]	= "Hide groups that only exisit to match with users";
		$this->obj_table->add_filter($structure);

		// load options
		$this->obj_table->load_options_form();


		// fetch data
		$this->obj_table->init_data_ldap();
		$this->obj_table->load_data_ldap("cn=*", "ou=Group,". $GLOBALS["config"]["ldap_dn"]);


		/*
			apply hide user group map filter

			This is tricky, we do so by fetching an array of all the gidnumbers for users
			and then running through the results and removing any groups that match gid number and
			have no users assigned to them.
		*/

		if (!empty($this->obj_table->filter["filter_hide_user_group_maps"]["defaultvalue"]))
		{
			// fetch user list
			$obj_ldap				= New ldap_query;
			$obj_ldap->connect();
			$obj_ldap->srvcfg["base_dn"]		= "ou=People,". $GLOBALS["config"]["ldap_dn"];

			if ($obj_ldap->search("uid=*", array("gidnumber")))
			{
				// generate user list array
				$user_gid_array	= array();

				foreach ($obj_ldap->data as $data_ldap)
				{
					$user_gid_array[]	=  $data_ldap["gidnumber"][0];
				}
			}


			// run through group items
			for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
			{
				// if the gid of the current group is one of the user gids, hide it.
				if (in_array($this->obj_table->data[$i]["gidnumber"], $user_gid_array))
				{
					// only hide if there are no member users
					if (empty($this->obj_table->data[$i]["memberuid"]))
					{
						unset($this->obj_table->data[$i]);
					}
				}
			}
		
			// update the row number and order
			sort($this->obj_table->data);

			$this->obj_table->data_num_rows = count($this->obj_table->data);
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>GROUP MANAGEMENT</h3>";
		print "<p>This page allows you to create, edit or delete groups.</p>";

		// display options form
		$this->obj_table->render_options_form();

		// table data
		if (!count($this->obj_table->columns))
		{
			print "<p><b>Please select some valid options to display.</b></p>";
		}
		elseif (!$this->obj_table->data_num_rows)
		{
			print "<p><b>No groups that match your options were found.</b></p>";
		}
		else
		{
			// details link
			$structure = NULL;
			$structure["id"]["column"]	= "gidnumber";
			$this->obj_table->add_link("tbl_lnk_details", "group_management/group-view.php", $structure);

			// radius options (if enabled)
			if (sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS' LIMIT 1") != "disabled")
			{
				$structure = NULL;
				$structure["id"]["column"] = "gidnumber";
				$this->obj_table->add_link("tbl_lnk_radius", "group_management/group-radius.php", $structure);
			}

			// delete link
			$structure = NULL;
			$structure["id"]["column"]	= "gidnumber";
			$this->obj_table->add_link("tbl_lnk_delete", "group_management/group-delete.php", $structure);


			// display the table
			$this->obj_table->render_table_html();

		}

		// add link
		print "<p><a class=\"button\" href=\"index.php?page=group_management/group-add.php\">Add New Group</a></p>";

	}

}


?>
