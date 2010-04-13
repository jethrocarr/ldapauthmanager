<?php
/*
	user/user-view.php
	
	access: admin only

	Displays all the details of the user accounts and allows them to be adjusted.
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

		$this->obj_menu_nav->add_item("User Details", "page=user_management/user-view.php&id=". $this->obj_user->id ."", TRUE);
		$this->obj_menu_nav->add_item("User Groups", "page=user_management/user-permissions.php&id=". $this->obj_user->id ."");
		$this->obj_menu_nav->add_item("Radius Attributes", "page=user_management/user-radius.php&id=". $this->obj_user->id ."");
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
			Define form structure
		*/
		$this->obj_form = New form_input;
		$this->obj_form->formname = "user_view";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "user_management/user-edit-process.php";
		$this->obj_form->method = "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "username";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"]		= "gn";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]		= "sn";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"] 	= "uidnumber";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$structure["options"]["width"]	= "100";
		$this->obj_form->add_input($structure);
			
		$structure = NULL;
		$structure["fieldname"] 	= "gidnumber";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$structure["options"]["width"]	= "100";
		$this->obj_form->add_input($structure);
			
		$structure = NULL;
		$structure["fieldname"] 	= "loginshell";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"] 	= "homedirectory";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);
		

		// passwords
		$structure = NULL;
		$structure["fieldname"]		= "password_message";
		$structure["type"]		= "message";
		$structure["defaultvalue"]	= "<i>Only input a password if you wish to change the existing one.</i>";
		$this->obj_form->add_input($structure);
			
			
		$structure = NULL;
		$structure["fieldname"]		= "password";
		$structure["type"]		= "password";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure["fieldname"]		= "password_confirm";
		$structure["type"]		= "password";
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
		$this->obj_form->subforms["user_view"]		= array("username", "gn", "sn", "uidnumber", "gidnumber", "loginshell", "homedirectory");
		$this->obj_form->subforms["user_password"]	= array("password_message", "password", "password_confirm");
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
				$this->obj_form->structure["gn"]["defaultvalue"]		= $this->obj_user->data["gn"];
				$this->obj_form->structure["sn"]["defaultvalue"]		= $this->obj_user->data["sn"];
				$this->obj_form->structure["uidnumber"]["defaultvalue"]		= $this->obj_user->data["uidnumber"];
				$this->obj_form->structure["gidnumber"]["defaultvalue"]		= $this->obj_user->data["gidnumber"];
				$this->obj_form->structure["loginshell"]["defaultvalue"]	= $this->obj_user->data["loginshell"];
				$this->obj_form->structure["homedirectory"]["defaultvalue"]	= $this->obj_user->data["homedirectory"];

			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>USER DETAILS</h3><br>";
		print "<p>This page allows you to view and adjust the user account details.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
