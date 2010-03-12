<?php
/*
	group_management/group-radius.php
	
	access: admin only

	Displays and allows adjustment of radius attributes for the selected group.
*/

class page_output
{
	var $obj_group;
	var $obj_menu_nav;
	var $obj_form;

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
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname = "group_radius";
		$this->obj_form->language = $_SESSION["group"]["lang"];

		$this->obj_form->action = "group_management/group-radius-process.php";
		$this->obj_form->method = "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "groupname";
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
		$structure["fieldname"] 	= "id_group";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_group->id;
		$this->obj_form->add_input($structure);
			
		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define basis subforms
		$this->obj_form->subforms["hidden"]		= array("id_group");
		$this->obj_form->subforms["submit"]		= array("submit");


		// import data from LDAP
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
		else
		{
			// load from LDAP
			if ($this->obj_group->load_data())
			{
				// general data
				$this->obj_form->structure["groupname"]["defaultvalue"]			= $this->obj_group->data["cn"];

				// radius attributes
				foreach ($radius_attributes as $attribute)
				{
					$this->obj_form->structure[ $attribute ]["defaultvalue"]	= $this->obj_group->data[ $attribute ];
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
