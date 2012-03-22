<?php
/*
	admin/config.php
	
	access: ldapadmins only

	Allows administrators to change system-wide settings stored in the config table that affect
	the key operation of the application.
*/

class page_output
{
	var $obj_form;


	function check_permissions()
	{
		return user_permissions_get("ldapadmins");
	}

	function check_requirements()
	{
		// nothing to do
		return 1;
	}


	function execute()
	{
		/*
			Define form structure
		*/
		
		$this->obj_form = New form_input;
		$this->obj_form->formname = "config";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "admin/config-process.php";
		$this->obj_form->method = "post";


		// seed options
		$structure = NULL;
		$structure["fieldname"]				= "AUTO_INT_UID";
		$structure["type"]				= "input";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "AUTO_INT_GID";
		$structure["type"]				= "input";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		// feature options
		$structure = NULL;
		$structure["fieldname"]				= "FEATURE_ZONES";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Enable to provide zone configuration options";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "FEATURE_RADIUS";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Enable or disable ability to set radius attributes in LDAP database for user accounts";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
	
		$structure = NULL;
		$structure["fieldname"]				= "FEATURE_RADIUS_MIKROTIK";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= " Enable Mikrotik vendor specific radius attributes";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "FEATURE_RADIUS_MAXVENDOR";
		$structure["type"]				= "input";
		$structure["options"]["label"]			= " Max-number of vendor attribute fields";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);



		// credential storage options
		$structure = NULL;
		$structure["fieldname"]				= "AUTH_USERPASSWORD_INFO";
		$structure["type"]				= "message";
		$structure["defaultvalue"]			= "<p>". lang_trans("AUTH_PASSWORD_INFO") ."</p>";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "AUTH_USERPASSWORD_TYPE";
		$structure["type"]				= "radio";
		$structure["values"]				= array("CLEAR_SIMPLE", "CLEAR_HEADER", "SSHA");
		$structure["translations"]["CLEAR_SIMPLE"]	= "cleartext: password";
		$structure["translations"]["CLEAR_HEADER"]	= "cleartext: {crypt}password";
		$structure["translations"]["SSHA"]		= "Salted SHA: {SSHA}EDELKvsEL2q+jOtKBWLp+ht+DFWvgVYo";
		$this->obj_form->add_input($structure);

		// TODO: future NT-Password and sambaPassword options to go here as checkboxes to enable.


		// security options
		$structure = NULL;
		$structure["fieldname"]				= "BLACKLIST_ENABLE";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Enable to prevent brute-force login attempts";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "BLACKLIST_LIMIT";
		$structure["type"]				= "input";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		
		// logging options
		$structure = NULL;
		$structure["fieldname"]					= "FEATURE_LOGS_ENABLE";
		$structure["type"]					= "checkbox";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$structure["options"]["label"]				= " Enable logging functionality.";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "LOG_UPDATE_INTERVAL";
		$structure["type"]					= "input";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$structure["options"]["label"]				= " seconds";
		$structure["options"]["width"]				= "50";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "LOG_RETENTION_PERIOD";
		$structure["type"]					= "input";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$structure["options"]["label"]				= " Delete logs after defined number of days (0 to disable).";
		$structure["options"]["width"]				= "50";
		$this->obj_form->add_input($structure);


		$this->obj_form->add_action("FEATURE_LOGS_ENABLE", "default", "LOG_UPDATE_INTERVAL", "hide");
		$this->obj_form->add_action("FEATURE_LOGS_ENABLE", "default", "LOG_RETENTION_PERIOD", "hide");

		$this->obj_form->add_action("FEATURE_LOGS_ENABLE", "1", "LOG_RETENTION_PERIOD", "show");
		$this->obj_form->add_action("FEATURE_LOGS_ENABLE", "1", "LOG_UPDATE_INTERVAL", "show");



		// misc	
		$structure = form_helper_prepare_timezonedropdown("TIMEZONE_DEFAULT");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure["fieldname"]				= "DATEFORMAT";
		$structure["type"]				= "radio";
		$structure["values"]				= array("yyyy-mm-dd", "mm-dd-yyyy", "dd-mm-yyyy");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);


		// submit section
		$structure = NULL;
		$structure["fieldname"]				= "submit";
		$structure["type"]				= "submit";
		$structure["defaultvalue"]			= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["config_seed"]		= array("AUTO_INT_UID", "AUTO_INT_GID");
		$this->obj_form->subforms["config_features"]		= array("FEATURE_ZONES", "FEATURE_RADIUS", "FEATURE_RADIUS_MIKROTIK", "FEATURE_RADIUS_MAXVENDOR");
		$this->obj_form->subforms["config_credentials"]		= array("AUTH_USERPASSWORD_INFO", "AUTH_USERPASSWORD_TYPE");
		$this->obj_form->subforms["config_security"]		= array("BLACKLIST_ENABLE", "BLACKLIST_LIMIT");
		$this->obj_form->subforms["config_dateandtime"]		= array("DATEFORMAT", "TIMEZONE_DEFAULT");
		$this->obj_form->subforms["config_logging"]		= array("FEATURE_LOGS_ENABLE", "LOG_RETENTION_PERIOD", "LOG_UPDATE_INTERVAL");
		$this->obj_form->subforms["submit"]			= array("submit");


		if ($_SESSION["error"]["message"])
		{
			// load error datas
			$this->obj_form->load_data_error();
		}
		else
		{
			// fetch all the values from the database
			$sql_config_obj		= New sql_query;
			$sql_config_obj->string	= "SELECT name, value FROM config ORDER BY name";
			$sql_config_obj->execute();
			$sql_config_obj->fetch_array();

			foreach ($sql_config_obj->data as $data_config)
			{
				$this->obj_form->structure[ $data_config["name"] ]["defaultvalue"] = $data_config["value"];
			}

			unset($sql_config_obj);
		}
	}



	function render_html()
	{
		// Title + Summary
		print "<h3>CONFIGURATION</h3><br>";
		print "<p>Use this page to adjust authldapmanager's configuration to suit your requirements.</p>";
	
		// display the form
		$this->obj_form->render_form();
	}

	
}

?>
