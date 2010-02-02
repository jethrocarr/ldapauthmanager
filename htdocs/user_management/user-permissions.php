<?php
/*
	user/user-permissions.php
	
	access: ldapadmins only

	Displays all the permissions/groups of the selected LDAP user and allows them to be configured.
*/


class page_output
{
	var $obj_user;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_user = New ldap_auth_manage_user;

		// fetch variables
		$this->obj_user->id = security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("User Details", "page=user_management/user-view.php&id=". $this->obj_user->id ."");
		$this->obj_menu_nav->add_item("User Groups", "page=user_management/user-permissions.php&id=". $this->obj_user->id ."", TRUE);
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

		return 1;
	}




	function execute()
	{
		/*
			Load User Information
		*/
		$this->obj_user->load_data();



		/*
			Define form structure
		*/
		$this->obj_form			= New form_input;
		$this->obj_form->formname	= "user_groups";
		$this->obj_form->language	= $_SESSION["group"]["lang"];

		$this->obj_form->action		= "user_management/user-permissions-process.php";
		$this->obj_form->method		= "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "username";
		$structure["type"]		= "text";
		$structure["defaultvalue"]	= $this->obj_user->data["uid"];
		$this->obj_form->add_input($structure);
							

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
	

		// define subforms
		$this->obj_form->subforms["user_view"]		= array("username");



		// get a list of all the groups
		$obj_ldap_groups				= New ldap_query;
		$obj_ldap_groups->connect();
		$obj_ldap_groups->srvcfg["base_dn"]		= "ou=Group,". $GLOBALS["config"]["ldap_dn"];

		if ($obj_ldap_groups->search("cn=*", array("gidnumber", "cn")))
		{
			// add items
			foreach ($obj_ldap_groups->data as $data_group)
			{
				if ($data_group["cn"][0])
				{
					$structure = NULL;
					$structure["fieldname"]				= "memberuid_". $data_group["gidnumber"][0];
					$structure["type"]				= "checkbox";
					$structure["options"]["label"]			= $data_group["cn"][0];
					$structure["options"]["no_fieldname"]		= "yes";

					// add checkbox
					$this->obj_form->add_input($structure);

					// add checkbox to subforms
					$this->obj_form->subforms["user_groups"][]	= "memberuid_". $data_group["gidnumber"][0];
				}
			}

		} // end if groups
	


		// define subforms
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
				$this->obj_form->structure["username"]["defaultvalue"]		= $this->obj_user->data["uid"];


				// check all member users
				$gidmembership = $this->obj_user->load_data_groups();

				if (count($gidmembership))
				{
					foreach ($gidmembership as $gid)
					{
						$this->obj_form->structure["memberuid_". $gid]["defaultvalue"] = "on";
					}
				}
			}
		}



		return 1;
	}


	function render_html()
	{
		// title + summary
		print "<h3>USER GROUPS/PERMISSIONS</h3><br>";
		print "<p>This page allows you to define what groups that the user can belong to.</p>";


		// display the form
		$this->obj_form->render_form();

		
	}

}

?>
