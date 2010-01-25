<?php
/*
	group/group-view.php
	
	access: ldapadmins only

	Displays all the details of the groups and allows them to be adjusted.
*/

class page_output
{
	var $obj_group;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_group		= New ldap_auth_manage_group;

		// fetch variables
		$this->obj_group->id = security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Group Details", "page=group_management/group-view.php&id=". $this->obj_group->id ."", TRUE);
//		$this->obj_menu_nav->add_item("Group Members", "page=group_management/group-permissions.php&id=". $this->obj_group->id ."");
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

		return 1;
	}



	function execute()
	{
		/*
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname = "group_view";
		$this->obj_form->language = $_SESSION["group"]["lang"];

		$this->obj_form->action = "group_management/group-edit-process.php";
		$this->obj_form->method = "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "groupname";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"] 	= "gidnumber";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$structure["options"]["width"]	= "100";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"] 	= "memberuid";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


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
	

		// define subforms
		$this->obj_form->subforms["group_view"]		= array("groupname", "gidnumber", "memberuid");



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
					$structure["fieldname"]				= "memberuid_". $data_user["uid"][0];
					$structure["type"]				= "checkbox";
					$structure["options"]["label"]			= $data_user["uid"][0];
					$structure["options"]["no_fieldname"]		= "yes";

					// add checkbox
					$this->obj_form->add_input($structure);

					// add checkbox to subforms
					$this->obj_form->subforms["group_members"][]	= "memberuid_". $data_user["uid"][0];
				}
			}
		} // end if users
	

		// define subforms
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
				$this->obj_form->structure["groupname"]["defaultvalue"]		= $this->obj_group->data["cn"];
				$this->obj_form->structure["gidnumber"]["defaultvalue"]		= $this->obj_group->data["gidnumber"];
//				$this->obj_form->structure["memberuid"]["defaultvalue"]		= format_arraytocommastring($this->obj_group->data["memberuid"]);

				// check all member users
				if (isset($this->obj_group->data["memberuid"]))
				{
					foreach ($this->obj_group->data["memberuid"] as $useruid)
					{
						$this->obj_form->structure["memberuid_". $useruid]["defaultvalue"] = "on";
					}
				}




			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>GROUP DETAILS</h3><br>";
		print "<p>This page allows you to view and adjust the group.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
