<?php
/*
	user_management/users.php

	access:
		ldapadmins

	Administrator-only tool for configuring LDAP user accounts.
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
		$this->obj_table->tablename	= "user_list";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "username", "uid");
		$this->obj_table->add_column("standard", "realname", "cn");
		$this->obj_table->add_column("standard", "uidnumber", "uidnumber");
		$this->obj_table->add_column("standard", "gidnumber", "uidnumber");

		// defaults
		$this->obj_table->columns		= array("username", "realname", "uidnumber", "gidnumber");
		$this->obj_table->columns_order		= array("username");
		$this->obj_table->columns_order_options	= array("username", "realname", "uidnumber", "gidnumber");

		// acceptable filter options
		//$structure = NULL;
		//$structure["fieldname"] = "searchbox";
		//$structure["type"]	= "input";
		//$structure["sql"]	= "username LIKE '%value%' OR realname LIKE '%value%' OR contact_email LIKE '%value%'";
		//$this->obj_table->add_filter($structure);


		// load options
		// $this->obj_table->load_options_form();

		$this->obj_table->init_data_ldap();
		$this->obj_table->load_data_ldap("uid=*", "ou=People,". $GLOBALS["config"]["ldap_dn"]);

	}


	function render_html()
	{
		// title + summary
		print "<h3>USER MANAGEMENT</h3>";
		print "<p>This page allows you to create, edit or delete user accounts, as well as allowing you to define the the account permissions.</p>";

		// display options form
		//$this->obj_table->render_options_form();

		// table data
		if (!count($this->obj_table->columns))
		{
			print "<p><b>Please select some valid options to display.</b></p>";
		}
		elseif (!$this->obj_table->data_num_rows)
		{
			print "<p><b>No users that match your options were found.</b></p>";
		}
		else
		{
			// details link
			$structure = NULL;
			$structure["id"]["column"]	= "uidnumber";
			$this->obj_table->add_link("tbl_lnk_details", "user_management/user-view.php", $structure);

			// permissions/groups
			$structure = NULL;
			$structure["id"]["column"]	= "gidnumber";
			$this->obj_table->add_link("tbl_lnk_permissions", "user_management/user-permissions.php", $structure);

			// delete link
			$structure = NULL;
			$structure["id"]["column"]	= "uidnumber";
			$this->obj_table->add_link("tbl_lnk_delete", "user_management/user-delete.php", $structure);


			// display the table
			$this->obj_table->render_table_html();

		}

		// add link
		print "<p><a class=\"button\" href=\"index.php?page=user_management/user-add.php\">Add New User</a></p>";

	}

}


?>
