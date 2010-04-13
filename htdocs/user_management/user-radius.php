<?php
/*
	user/user-radius.php
	
	access: admin only

	Displays and allows adjustment of radius attributes for the selected user account.
*/

class page_output
{
	var $obj_user;
	var $obj_menu_nav;
	var $obj_form;

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
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname = "user_radius";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "user_management/user-radius-process.php";
		$this->obj_form->method = "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "username";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);
		

		// built in attributes
	
		$structure = NULL;
		$structure["fieldname"]		= "radius_attr_about";
		$structure["type"]		= "message";
		$structure["defaultvalue"]	= "Below is a full list of all attributes supported by the LDAP server, leaving any blank will cause radius to fall back to configured defaults. You can define customer or vendor attributes in the section below.";
		$this->obj_form->add_input($structure);

		$radius_attributes = radius_attr_standard();

		foreach ($radius_attributes as $attribute)
		{
			$structure = NULL;
			$structure["fieldname"]		= $attribute;
			$structure["type"]		= "input";
			$this->obj_form->add_input($structure);
		}


		// define subforms
		$this->obj_form->subforms["user_view"]		= array("username");
		$this->obj_form->subforms["radius_attr"]	= array("radius_attr_about");
		
		foreach ($radius_attributes as $attribute)
		{
			$this->obj_form->subforms["radius_attr"][] = $attribute;
		}


		// vendor specific attributes
		$structure = NULL;
		$structure["fieldname"]		= "vendor_attr_about";
		$structure["type"]		= "message";
		$structure["defaultvalue"]	= "Define vendor-specific radius attributes below as either check or reply attributes in the form of &lt;radius-attribute&gt; &lt;operator&gt; &lt;value&gt;";
		$this->obj_form->add_input($structure);


		$this->obj_form->subforms["radius_attr_vendor"]	= array("vendor_attr_about");

		for ($i=0; $i < $this->num_vendor_fields; $i++)
		{
			$structure = NULL;
			$structure["fieldname"]		= "vendor_attr_reply_$i";
			$structure["type"]		= "input";
			$structure["options"]["width"]	= "500";
			$this->obj_form->add_input($structure);

			$this->obj_form->subforms["radius_attr_vendor"][] = "vendor_attr_reply_$i";
		}

		for ($i=0; $i < $this->num_vendor_fields; $i++)
		{
			$structure = NULL;
			$structure["fieldname"]		= "vendor_attr_check_$i";
			$structure["type"]		= "input";
			$structure["options"]["width"]	= "500";
			$this->obj_form->add_input($structure);

			$this->obj_form->subforms["radius_attr_vendor"][] = "vendor_attr_check_$i";
		}



		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_user";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_user->id;
		$this->obj_form->add_input($structure);
			
		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		


		// define basis subforms
		$this->obj_form->subforms["hidden"]		= array("id_user");
		$this->obj_form->subforms["submit"]		= array("submit");


		// import data from LDAP
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
				$this->obj_form->structure["username"]["defaultvalue"]			= $this->obj_user->data["uid"];

				// radius attributes
				foreach ($radius_attributes as $attribute)
				{
					$this->obj_form->structure[ $attribute ]["defaultvalue"]	= $this->obj_user->data[ $attribute ];
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
