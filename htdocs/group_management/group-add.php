<?php
/*
	group/group-add.php
	
	access: ldapadmins only

	Allows a new group to be created.
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
		$this->obj_form->formname = "group_add";
		$this->obj_form->language = $_SESSION["group"]["lang"];

		$this->obj_form->action = "group_management/group-edit-process.php";
		$this->obj_form->method = "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 		= "groupname";
		$structure["type"]			= "input";
		$structure["options"]["req"]		= "yes";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"] 		= "gidnumber";
		$structure["type"]			= "input";
		$structure["options"]["req"]		= "yes";
		$structure["options"]["width"]		= "100";
		$structure["options"]["label"]		= " <i>(leave blank for automatic generation)</i>";
		$this->obj_form->add_input($structure);
			
		// submit section
		$structure = NULL;
		$structure["fieldname"] 		= "submit";
		$structure["type"]			= "submit";
		$structure["defaultvalue"]		= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["group_add"]		= array("groupname", "gidnumber");
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
		print "<h3>GROUP ADD</h3><br>";
		print "<p>This page allows you to add a new group to the LDAP database.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
