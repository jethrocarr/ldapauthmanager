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

		$this->obj_table->language	= $_SESSION["group"]["lang"];
		$this->obj_table->tablename	= "group_list";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "groupname", "cn");
		$this->obj_table->add_column("standard", "gidnumber", "gidnumber");
		$this->obj_table->add_column("standard", "memberuid", "memberuid");

		// defaults
		$this->obj_table->columns		= array("groupname", "gidnumber", "memberuid");
		$this->obj_table->columns_order		= array("groupname");
		$this->obj_table->columns_order_options	= array("groupname", "gidnumber", "memberuid");

		// acceptable filter options
		//$structure = NULL;
		//$structure["fieldname"] = "searchbox";
		//$structure["type"]	= "input";
		//$structure["sql"]	= "groupname LIKE '%value%' OR realname LIKE '%value%' OR contact_email LIKE '%value%'";
		//$this->obj_table->add_filter($structure);


		// load options
		// $this->obj_table->load_options_form();

		$this->obj_table->init_data_ldap();
		$this->obj_table->load_data_ldap("cn=*", "ou=Group,". $GLOBALS["config"]["ldap_dn"]);

	}


	function render_html()
	{
		// title + summary
		print "<h3>GROUP MANAGEMENT</h3>";
		print "<p>This page allows you to create, edit or delete groups.</p>";

		// display options form
		//$this->obj_table->render_options_form();

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

//			// permissions/groups
//			$structure = NULL;
//			$structure["id"]["column"]	= "gidnumber";
//			$this->obj_table->add_link("tbl_lnk_permissions", "group_management/group-groups.php", $structure);

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
