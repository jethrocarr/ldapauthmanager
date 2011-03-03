<?php
/*
	user/user-radius.php
	
	access: admin only

	Displays and allows adjustment of radius attributes for the selected user account.
*/

class page_output extends ui_radius_attributes
{
	var $obj_user;
	var $obj_menu_nav;

	var $num_vendor_fields;

	function page_output()
	{

		// initate object
		$this->obj_user			= New ldap_auth_manage_user;

		// fetch variables
		$this->obj_user->id		= security_script_input('/^[0-9]*$/', $_GET["id"]);

		// fetch configuration
		$this->num_vendor_fields	= sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS_MAXVENDOR'");


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("User Details", "page=user_management/user-view.php&id=". $this->obj_user->id ."");
		$this->obj_menu_nav->add_item("User Groups", "page=user_management/user-permissions.php&id=". $this->obj_user->id ."");
		$this->obj_menu_nav->add_item("Radius Attributes", "page=user_management/user-radius.php&id=". $this->obj_user->id ."", TRUE);
		$this->obj_menu_nav->add_item("Delete User", "page=user_management/user-delete.php&id=". $this->obj_user->id ."");
	}


	function check_permissions()
	{
		return user_permissions_get("ldapadmins");
	}


	function check_requirements()
	{
		// make sure the LDAP user requested actually exists.
		if (!$this->obj_user->verify_id())
		{
			log_write("error", "page_output", "The requested user (". $this->obj_user->id .") does not exist - possibly the user has been deleted?");
			return 0;
		}

		// make sure that the feature is enabled
		if ($GLOBALS["config"]["FEATURE_RADIUS"] == "disabled")
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

		$this->obj_form->formname = "user_radius";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "user_management/user-radius-process.php";
		$this->obj_form->method = "post";



		/*
			Load Form Data
		*/

		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
		else
		{
			// load from LDAP
			if ($this->obj_user->load_data())
			{
				// general data
				$this->obj_form->structure["id_object"]["defaultvalue"]			= $this->obj_user->id;
				$this->obj_form->structure["username"]["defaultvalue"]			= $this->obj_user->data["uid"];

				// radius attributes
				foreach (radius_attr_standard() as $attribute)
				{
					$this->obj_form->structure[ $attribute ]["defaultvalue"]	= $this->obj_user->data[ $attribute ];
				}

				// radius: vendor specific: Mikrotik
				if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
				{
					foreach (array_keys(radius_attr_mikrotik()) as $attribute)
					{
						$this->obj_form->structure[ $attribute ]["defaultvalue"]	= $this->obj_user->data[ $attribute ];
					}
				}
	
				// vendor attributes
				for ($i=0; $i < $this->num_vendor_fields; $i++)
				{
					$this->obj_form->structure["vendor_attr_check_$i"]["defaultvalue"]	= htmlentities($this->obj_user->data["radiusCheckItem"][$i]);
					$this->obj_form->structure["vendor_attr_reply_$i"]["defaultvalue"]	= htmlentities($this->obj_user->data["radiusReplyItem"][$i]);
				}


				// legacy data safety check
				if (in_array("account", $this->obj_user->data["objectclass"]))
				{
					log_write("error", "process", "This user needs to be upgraded to use inetOrgPerson before radius attributes can be changed.");
					return 0;
				}
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>USER RADIUS ATTRIBUTES</h3><br>";
		print "<p>Define and view all radius attributes for the selected user here.</p>";


		// display the form
		$this->obj_form->render_form();
	}

}

?>
