<?php
/*
	group_management/group-radius.php
	
	access: admin only

	Displays and allows adjustment of radius attributes for the selected group.
*/

class page_output extends ui_radius_attributes
{
	var $obj_group;
	var $obj_menu_nav;

	var $num_vendor_fields;


	function page_output()
	{

		// initate object
		$this->obj_group		= New ldap_auth_manage_group;

		// fetch variables
		$this->obj_group->id		= security_script_input('/^[0-9]*$/', $_GET["id"]);

		// fetch configuration
		$this->num_vendor_fields	= sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS_MAXVENDOR'");


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Group Details", "page=group_management/group-view.php&id=". $this->obj_group->id ."");
		$this->obj_menu_nav->add_item("Radius Attributes", "page=group_management/group-radius.php&id=". $this->obj_group->id ."", TRUE);
		$this->obj_menu_nav->add_item("Delete Group", "page=group_management/group-delete.php&id=". $this->obj_group->id ."");
	}


	function check_permissions()
	{
		return user_permissions_get("ldapadmins");
	}


	function check_requirements()
	{
		// make sure the LDAP group requested actually exists.
		if (!$this->obj_group->verify_id())
		{
			log_write("error", "page_output", "The requested group (". $this->obj_group->id .") does not exist - possibly the group has been deleted?");
			return 0;
		}

		// make sure that the feature is enabled
		if (sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS' LIMIT 1") == "disabled")
		{
			log_write("error", "page_output", "Radius attribute configuration has been disabled by the administrator. Use the admin configuration to page to enable it if required.");
			return 0;
		}

		return 1;
	}



	function execute()
	{
		/*
			Load Form
		*/

		$this->ui_form();


		/*
			Define page-specific form structure
		*/

		$this->obj_form->formname = "group_radius";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "group_management/group-radius-process.php";
		$this->obj_form->method = "post";



		/*
			Load Form Data
		*/

		if (error_check())
		{
			// automated data loads
			$this->obj_form->load_data_error();
		}
		else
		{
			// load from LDAP
			if ($this->obj_group->load_data())
			{
				// general data
				$this->obj_form->structure["id_object"]["defaultvalue"]			= $this->obj_group->id;
				$this->obj_form->structure["objectname"]["defaultvalue"]		= $this->obj_group->data["cn"];

				// radius attributes
				foreach (radius_attr_standard() as $attribute)
				{
					$this->obj_form->structure[ $attribute ]["defaultvalue"]	= $this->obj_group->data[ $attribute ];
				}

				// radius: vendor specific: Mikrotik
				if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
				{
					foreach (array_keys(radius_attr_mikrotik()) as $attribute)
					{
						$this->obj_form->structure[ $attribute ]["defaultvalue"]	= $this->obj_group->data[ $attribute ];
					}
				}

				// vendor attributes
				for ($i=0; $i < $this->num_vendor_fields; $i++)
				{
					$this->obj_form->structure["vendor_attr_check_$i"]["defaultvalue"]	= htmlentities($this->obj_group->data["radiusCheckItem"][$i]);
					$this->obj_form->structure["vendor_attr_reply_$i"]["defaultvalue"]	= htmlentities($this->obj_group->data["radiusReplyItem"][$i]);
				}
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>GROUP RADIUS ATTRIBUTES</h3><br>";
		print "<p>Define and view all radius attributes for the selected group here. Note that in FreeRadius, only one group can be assigned per NAS, so you will have to set all attributes in one group, rather than being able to merge attributes from several groups.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
