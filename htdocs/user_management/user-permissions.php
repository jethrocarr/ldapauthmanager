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
			Define form structure
		*/
		/*
		$this->obj_form = New form_input;
		$this->obj_form->formname = "user_permissions";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "user_management/user-permissions-process.php";
		$this->obj_form->method = "post";


		$sql_perms_obj		= New sql_query;
		$sql_perms_obj->string	= "SELECT * FROM `permissions` ORDER BY value='disabled' DESC, value='admin' DESC, value";
		$sql_perms_obj->execute();
		$sql_perms_obj->fetch_array();
		
		foreach ($sql_perms_obj->data as $data_perms)
		{
			// define the checkbox
			$structure = NULL;
			$structure["fieldname"]				= $data_perms["value"];
			$structure["type"]				= "checkbox";
			$structure["options"]["label"]			= $data_perms["description"];
			$structure["options"]["no_translate_fieldname"]	= "yes";

			// check if the user has this permission
			$sql_obj		= New sql_query;
			$sql_obj->string	= "SELECT id FROM `users_permissions` WHERE userid='". $this->id ."' AND permid='". $data_perms["id"] ."'";
			$sql_obj->execute();

			if ($sql_obj->num_rows())
			{
				$structure["defaultvalue"] = "on";
			}

			// add checkbox
			$this->obj_form->add_input($structure);

			// add checkbox to subforms
			$this->obj_form->subforms["user_permissions"][] = $data_perms["value"];

		}
	
		// user ID (hidden field)
		$structure = NULL;
		$structure["fieldname"]		= "id_user";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->id;
		$this->obj_form->add_input($structure);	
	
		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["hidden"]		= array("id_user");
		$this->obj_form->subforms["submit"]		= array("submit");

		
		/*
			Note: We don't load from error data, since there should never
			be any errors when using this form.
		*/

		return 1;
	}


	function render_html()
	{
		// title + summary
		print "<h3>USER GROUPS/PERMISSIONS</h3><br>";
		print "<p>This page allows you to define what groups that the user can belong to.</p>";


		// display the form
		//$this->obj_form->render_form();

		format_msgbox("important", "<p>This feature hasn't been implemented yet, you can add the user to the desired group using the Manage Groups page meanwhile</p>");

	}

}

?>
