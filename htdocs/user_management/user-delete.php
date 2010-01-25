<?php
/*
	user_management/user-delete.php
	
	access:	ldapadmins only

	Allows an unwanted user to be deleted.
*/


class page_output
{
	var $obj_user;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{
		// initate object
		$this->obj_user		= New ldap_auth_manage_user;

		// fetch variables
		$this->obj_user->id = security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("User Details", "page=user_management/user-view.php&id=". $this->obj_user->id ."");
		$this->obj_menu_nav->add_item("User Groups", "page=user_management/user-permissions.php&id=". $this->obj_user->id ."");
		$this->obj_menu_nav->add_item("Delete User", "page=user_management/user-delete.php&id=". $this->obj_user->id ."", TRUE);
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
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname = "user_delete";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "user_management/user-delete-process.php";
		$this->obj_form->method = "post";
		

		// general
		$structure = NULL;
		$structure["fieldname"] 	= "username";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		// hidden
		$structure = NULL;
		$structure["fieldname"] 	= "id_user";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_user->id;
		$this->obj_form->add_input($structure);
		
		
		// confirm delete
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this user and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);



		// define submit field
		$structure = NULL;
		$structure["fieldname"]		= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "delete";
		$this->obj_form->add_input($structure);


		
		// define subforms
		$this->obj_form->subforms["user_delete"]	= array("username");
		$this->obj_form->subforms["hidden"]		= array("id_user");
		$this->obj_form->subforms["submit"]		= array("delete_confirm", "submit");

	
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
			}
		}
	

	}


	function render_html()
	{
		// title + summary
		print "<h3>DELETE USER</h3><br>";
		print "<p>This page allows you to delete the selected user account.</p>";

		// display the form
		$this->obj_form->render_form();
	}

}

?>
