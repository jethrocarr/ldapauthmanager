<?php
/*
	zone/zone-view.php
	
	access: ldapadmins only

	Displays all the details of the zones and allows them to be adjusted.
*/

class page_output
{
	var $obj_zone;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_zone			= New ldap_auth_manage_zone;

		// fetch variables
		$this->obj_zone->data["cn"]	= security_script_input('/^\S*$/', $_GET["cn"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Zone Details", "page=zone_management/zone-view.php&cn=". $this->obj_zone->data["cn"] ."", TRUE);
		$this->obj_menu_nav->add_item("Delete Zone", "page=zone_management/zone-delete.php&cn=". $this->obj_zone->data["cn"] ."");
	}


	function check_permissions()
	{
		return user_permissions_get("ldapadmins");
	}


	function check_requirements()
	{
		// make sure the LDAP zone requested actually exists.
		if (!$this->obj_zone->verify_zonename( $this->obj_zone->data["cn"] ))
		{
			log_write("error", "page_output", "The requested zone (". $this->obj_zone->data["cn"] .") does not exist - possibly the zone has been deleted?");
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
		$this->obj_form->formname = "zone_view";
		$this->obj_form->language = $_SESSION["zone"]["lang"];

		$this->obj_form->action = "zone_management/zone-edit-process.php";
		$this->obj_form->method = "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "zonename";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
							

		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "origzone";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_zone->data["cn"];
		$this->obj_form->add_input($structure);
			
		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
	

		// define subforms
		$this->obj_form->subforms["zone_view"]		= array("zonename");



		// get a list of all the users
		$obj_ldap_users				= New ldap_query;
		$obj_ldap_users->connect();
		$obj_ldap_users->srvcfg["base_dn"]	= "ou=People,". $GLOBALS["config"]["ldap_dn"];

		if ($obj_ldap_users->search("uid=*", array("uid")))
		{
			// add items
			foreach ($obj_ldap_users->data as $data_user)
			{
				if ($data_user["uid"][0])
				{
					$structure = NULL;
					$structure["fieldname"]				= "uniqueMember_". $data_user["uid"][0];
					$structure["type"]				= "checkbox";
					$structure["options"]["label"]			= $data_user["uid"][0];
					$structure["options"]["no_fieldname"]		= "yes";

					// add checkbox
					$this->obj_form->add_input($structure);

					// add checkbox to subforms
					$this->obj_form->subforms["zone_members"][]	= "uniqueMember_". $data_user["uid"][0];
				}
			}
		} // end if users
	

		// define subforms
		$this->obj_form->subforms["hidden"]		= array("origzone");
		$this->obj_form->subforms["submit"]		= array("submit");


		// import data from LDAP
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
		else
		{
			// load from LDAP
			if ($this->obj_zone->load_data())
			{
				$this->obj_form->structure["zonename"]["defaultvalue"]		= $this->obj_zone->data["cn"];

				// check all member users
				if (isset($this->obj_zone->data["uniqueMember"]))
				{
					foreach ($this->obj_zone->data["uniqueMember"] as $useruid)
					{
						// strip out the user name from the string
						if (preg_match("/^uid=(\S*?),/", $useruid, $matches))
						{
							$useruid = $matches[1];

							$this->obj_form->structure["uniqueMember_". $useruid]["defaultvalue"] = "on";
						}
					}
				}
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>ZONE DETAILS</h3><br>";
		print "<p>This page allows you to view and adjust the zone, including assigning/unassigning user accounts.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
