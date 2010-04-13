<?php
/*
	user/user-add.php
	
	access: ldapadmins only

	Allows a new user to be created.
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
		$this->obj_form->formname = "user_add";
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
		$structure["options"]["label"]	= " <i>(leave blank for automatic generation)</i>";
		$this->obj_form->add_input($structure);
			
		$structure = NULL;
		$structure["fieldname"] 	= "gidnumber";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$structure["options"]["width"]	= "100";
		$structure["options"]["label"]	= " <i>(leave blank for automatic generation)</i>";
		$this->obj_form->add_input($structure);
			
		$structure = NULL;
		$structure["fieldname"] 		= "loginshell";
		$structure["type"]			= "input";
		$structure["options"]["req"]		= "yes";
		$structure["defaultvalue"]		= "/bin/bash";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"] 	= "homedirectory";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$structure["options"]["label"]	= " <i>(leave blank for automatic generation)</i>";
		$this->obj_form->add_input($structure);
				

		// passwords	
		$structure = NULL;
		$structure["fieldname"]		= "password";
		$structure["type"]		= "password";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure["fieldname"]		= "password_confirm";
		$structure["type"]		= "password";
		$this->obj_form->add_input($structure);
	

		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["user_view"]		= array("username", "gn", "sn", "uidnumber", "gidnumber", "loginshell", "homedirectory");
		$this->obj_form->subforms["user_password"]	= array("password", "password_confirm");
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
		print "<h3>USER ADD</h3><br>";
		print "<p>This page allows you to add a new user to the LDAP database.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
