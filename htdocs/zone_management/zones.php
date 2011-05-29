<?php
/*
	zone_management/zones.php

	access:
		ldapadmins

	Administrator-only tool for configuring LDAP zones
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
		if (!$GLOBALS["config"]["FEATURE_ZONES"])
		{
			log_write("error", "page", "In order to use zone configuration, please enable FEATURE_ZONES on the configuration page");
			return 0;
		}

		return 1;
	}


	function execute()
	{
		// establish a new table object
		$this->obj_table = New table;

		$this->obj_table->language	= $_SESSION["user"]["lang"];
		$this->obj_table->tablename	= "zone_list";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "zonename", "cn");
		$this->obj_table->add_column("standard", "uniquemember", "uniquemember");

		// defaults
		$this->obj_table->columns		= array("zonename", "uniquemember");
//		$this->obj_table->columns_order		= array("zonename");
//		$this->obj_table->columns_order_options	= array("zonename", "memberuid");

		// acceptable filter options
		$structure = NULL;
		$structure["fieldname"] = "searchbox";
		$structure["type"]	= "input";
		$structure["ldap"]	= "(|(cn=value)(uniquemember=value))";
		$this->obj_table->add_filter($structure);

		// load options
		$this->obj_table->load_options_form();

		// fetch data
		$this->obj_table->init_data_ldap();
		$this->obj_table->load_data_ldap("cn=*", "ou=Zones,". $GLOBALS["config"]["ldap_dn"]);

		// re-format uniquemember rules
		for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
		{
			$useruids_orig	= explode(',', $this->obj_table->data[$i]["uniquemember"]);
			$useruids_new	= array();

			foreach ($useruids_orig as $useruid)
			{
				if (preg_match("/^\s*uid=(\S*)$/", $useruid, $matches))
				{
					$useruids_new[] = $matches[1];
				}
			}

			$this->obj_table->data[$i]["uniquemember"] = format_arraytocommastring($useruids_new);

		} // end of table data loop

	}


	function render_html()
	{
		// title + summary
		print "<h3>ZONES MANAGEMENT</h3>";
		print "<p>Zones are a special type of group - rather than having any of the regular POSIX style attributes, zone groups allows grouping of users in order to control access to specific systems - typically for the purposes of segregating staff access from certain systems without going to the extent of sub directories in LDAP.</p>";

		// display options form
		$this->obj_table->render_options_form();

		// table data
		if (!count($this->obj_table->columns))
		{
			print "<p><b>Please select some valid options to display.</b></p>";
		}
		elseif (!$this->obj_table->data_num_rows)
		{
			print "<p><b>No zones that match your options were found.</b></p>";
		}
		else
		{
			// details link
			$structure = NULL;
			$structure["cn"]["column"]	= "zonename";
			$this->obj_table->add_link("tbl_lnk_details", "zone_management/zone-view.php", $structure);

			// delete link
			$structure = NULL;
			$structure["cn"]["column"]	= "zonename";
			$this->obj_table->add_link("tbl_lnk_delete", "zone_management/zone-delete.php", $structure);


			// display the table
			$this->obj_table->render_table_html();

		}

		// add link
		print "<p><a class=\"button\" href=\"index.php?page=zone_management/zone-add.php\">Add New Zone Group</a></p>";

	}

}


?>
