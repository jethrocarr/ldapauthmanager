<?php
/*
	zone/zone-add.php
	
	access: ldapadmins only

	Allows a new zone to be created.
*/

class page_output
{
	var $obj_menu_nav;
	var $obj_form;


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
		/*
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname = "zone_add";
		$this->obj_form->language = $_SESSION["zone"]["lang"];

		$this->obj_form->action = "zone_management/zone-edit-process.php";
		$this->obj_form->method = "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 		= "zonename";
		$structure["type"]			= "input";
		$structure["options"]["req"]		= "yes";
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


		// submit section
		$structure = NULL;
		$structure["fieldname"] 		= "submit";
		$structure["type"]			= "submit";
		$structure["defaultvalue"]		= "Save Changes";
		$this->obj_form->add_input($structure);

		// define subforms
		$this->obj_form->subforms["submit"]		= array("submit");


		// import data from LDAP
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>ZONE ADD</h3><br>";
		print "<p>This page allows you to add a new zone to the LDAP database.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
