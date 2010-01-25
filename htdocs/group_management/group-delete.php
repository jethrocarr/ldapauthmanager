<?php
/*
	group_management/group-delete.php
	
	access:	ldapadmins only

	Allows an unwanted group to be deleted.
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

		$this->obj_menu_nav->add_item("Group Details", "page=group_management/group-view.php&id=". $this->obj_group->id ."");
//		$this->obj_menu_nav->add_item("User Groups", "page=group_management/group-permissions.php&id=". $this->obj_group->id ."");
		$this->obj_menu_nav->add_item("Delete Group", "page=group_management/group-delete.php&id=". $this->obj_group->id ."", TRUE);
	}


	function check_permissions()
	{
		return	user_permissions_get("ldapadmins");
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
		$this->obj_form->formname = "group_delete";
		$this->obj_form->language = $_SESSION["group"]["lang"];

		$this->obj_form->action = "group_management/group-delete-process.php";
		$this->obj_form->method = "post";
		

		// general
		$structure = NULL;
		$structure["fieldname"] 	= "groupname";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		// hidden
		$structure = NULL;
		$structure["fieldname"] 	= "id_group";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_group->id;
		$this->obj_form->add_input($structure);
		
		
		// confirm delete
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this group and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);



		// define submit field
		$structure = NULL;
		$structure["fieldname"]		= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "delete";
		$this->obj_form->add_input($structure);


		
		// define subforms
		$this->obj_form->subforms["group_delete"]	= array("groupname");
		$this->obj_form->subforms["hidden"]		= array("id_group");
		$this->obj_form->subforms["submit"]		= array("delete_confirm", "submit");

	
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
			}
		}
	

	}


	function render_html()
	{
		// title + summary
		print "<h3>DELETE GROUP</h3><br>";
		print "<p>This page allows you to delete the selected group.</p>";

		// display the form
		$this->obj_form->render_form();
	}

}

?>
