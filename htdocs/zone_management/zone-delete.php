<?php
/*
	zone_management/zone-delete.php
	
	access:	ldapadmins only

	Allows an unwanted zone to be deleted.
*/


class page_output
{
	var $obj_zone;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{
		// initate object
		$this->obj_zone		= New ldap_auth_manage_zone;

		// fetch variables
		$this->obj_zone->data["cn"]	= security_script_input('/^\S*$/', $_GET["cn"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Zone Details", "page=zone_management/zone-view.php&cn=". $this->obj_zone->data["cn"] ."");
		$this->obj_menu_nav->add_item("Delete Zone", "page=zone_management/zone-delete.php&cn=". $this->obj_zone->data["cn"] ."", TRUE);
	}


	function check_permissions()
	{
		return	user_permissions_get("ldapadmins");
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
		$this->obj_form->formname = "zone_delete";
		$this->obj_form->language = $_SESSION["zone"]["lang"];

		$this->obj_form->action = "zone_management/zone-delete-process.php";
		$this->obj_form->method = "post";
		

		// general
		$structure = NULL;
		$structure["fieldname"] 	= "zonename";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		// hidden
		$structure = NULL;
		$structure["fieldname"] 	= "origzone";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_zone->data["cn"];
		$this->obj_form->add_input($structure);
		
		
		// confirm delete
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this zone and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);



		// define submit field
		$structure = NULL;
		$structure["fieldname"]		= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "delete";
		$this->obj_form->add_input($structure);


		
		// define subforms
		$this->obj_form->subforms["zone_delete"]	= array("zonename");
		$this->obj_form->subforms["hidden"]		= array("origzone");
		$this->obj_form->subforms["submit"]		= array("delete_confirm", "submit");

	
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
			}
		}
	

	}


	function render_html()
	{
		// title + summary
		print "<h3>DELETE ZONE</h3><br>";
		print "<p>This page allows you to delete the selected zone.</p>";

		// display the form
		$this->obj_form->render_form();
	}

}

?>
