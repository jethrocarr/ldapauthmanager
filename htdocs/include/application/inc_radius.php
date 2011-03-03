<?php
/*
	inc_radius.php

	Provides functions for working with radius attributes and other data.
*/



/*
	radius_attr_standard

	Returns an array of all the standard radius attributes.

	TODO: this is currently set here in the code, but a better solution for the future would be
	      to query the LDAP database to determine all possible attributes.

	Values
	array		Array of standard attributes
*/

function radius_attr_standard()
{
	log_debug("inc_radius", "Executing radius_attr_standard()");

	$attributes = array('radiusArapFeatures', 'radiusArapSecurity', 'radiusArapZoneAccess', 'radiusAuthType', 'radiusCallbackId', 'radiusCallbackNumber', 'radiusCalledStationId', 'radiusCallingStationId', 'radiusClass', 'radiusClientIPAddress', 'radiusFilterId', 'radiusFramedAppleTalkLink', 'radiusFramedAppleTalkNetwork', 'radiusFramedAppleTalkZone', 'radiusFramedCompression', 'radiusFramedIPAddress', 'radiusFramedIPNetmask', 'radiusFramedIPXNetwork', 'radiusFramedMTU', 'radiusFramedProtocol', 'radiusFramedRoute', 'radiusFramedRouting', 'radiusGroupName', 'radiusHint', 'radiusHuntgroupName', 'radiusIdleTimeout', 'radiusLoginIPHost', 'radiusLoginLATGroup', 'radiusLoginLATNode', 'radiusLoginLATPort', 'radiusLoginLATService', 'radiusLoginService', 'radiusLoginTCPPort', 'radiusPasswordRetry', 'radiusPortLimit', 'radiusProfileDn', 'radiusPrompt', 'radiusProxyToRealm', 'radiusReplicateToRealm', 'radiusRealm', 'radiusServiceType', 'radiusSessionTimeout', 'radiusTerminationAction', 'radiusTunnelAssignmentId', 'radiusTunnelMediumType', 'radiusTunnelPassword', 'radiusTunnelPreference', 'radiusTunnelPrivateGroupId', 'radiusTunnelServerEndpoint', 'radiusTunnelType', 'radiusVSA', 'radiusTunnelClientEndpoint', 'radiusSimultaneousUse', 'radiusLoginTime', 'radiusUserCategory', 'radiusStripUserName', 'dialupAccess', 'radiusExpiration', 'radiusNASIpAddress', 'radiusReplyMessage');

	return $attributes;

} // end of radius_attr_standard





/*
	radius_attr_mikrotik

	Returns an associative array of the Mikrotik device attributes
	and their types.

	Values
	array		Array of attribute names
*/

function radius_attr_mikrotik()
{
	log_debug("inc_radius", "Executing radius_attr_mikrotik()");

	$attributes = array(
			'Mikrotik-Recv-Limit'			=> 'bytes',
			'Mikrotik-Recv-Limit-Gigawords'		=> 'gigaword',
			'Mikrotik-Xmit-Limit'			=> 'bytes',
			'Mikrotik-Xmit-Limit-Gigawords'		=> 'gigaword',
			'Mikrotik-Total-Limit'			=> 'bytes',
			'Mikrotik-Total-Limit-Gigawords'	=> 'gigaword',
			'Mikrotik-Rate-Limit'			=> 'string',
			'Mikrotik-Group'			=> 'string',
			'Mikrotik-Realm'			=> 'string',
			'Mikrotik-Host-IP'			=> 'ipaddr',
			'Mikrotik-Mark-Id'			=> 'string',
			'Mikrotik-Advertise-URL'		=> 'string',
			'Mikrotik-Advertise-Interval'		=> 'int',
			'Mikrotik-Address-List'			=> 'string',
			'Mikrotik-Wireless-PSK'			=> 'string',
			'Mikrotik-Wireless-Forward'		=> 'bool',
			'Mikrotik-Wireless-Skip-Dot1x'		=> 'bool',
			'Mikrotik-Wireless-Enc-Algo'		=> 'int',
			'Mikrotik-Wireless-Enc-Key'		=> 'string',
			'Mikrotik-Wireless-MPKey'		=> 'string',
			'Mikrotik-Wireless-Comment'		=> 'string'
		);

	return $attributes;

} // end of radius_attr_mikrotik




/*
	ui_radius_attributes

	Functional class providing UI forms and processing for radius attributes - this code is used in both
	user and group handling sections and is seporated out to reduce duplication.

	These functions are used by the class being extended by the actual page class and inheriting the variables
	and data structures.
*/

class ui_radius_attributes
{
	var $obj_form;		// form object
	var $obj_owner;		// user or group object - syntax & operation is the same


	/*
		ui_form

		Generate the form to be used for radius attributes.
	*/

	function ui_form()
	{
		log_write("debug", "ui_radius_attributes", "Executing ui_form()");


		// define the form object
		$this->obj_form = New form_input;


		// define the form attributes.


		// general
		$structure = NULL;
		$structure["fieldname"] 	= "objectname";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);
		

		// built in attributes
		$structure = NULL;
		$structure["fieldname"]		= "radius_attr_about";
		$structure["type"]		= "message";
		$structure["defaultvalue"]	= "Below is a full list of all attributes supported by the LDAP server, leaving any blank will cause radius to fall back to configured defaults. You can define customer or vendor attributes in the section below.";
		$this->obj_form->add_input($structure);

		$radius_attributes = radius_attr_standard();

		foreach ($radius_attributes as $attribute)
		{
			$structure = NULL;
			$structure["fieldname"]		= $attribute;
			$structure["type"]		= "input";
			$this->obj_form->add_input($structure);
		}


		// define subforms
		$this->obj_form->subforms["user_view"]		= array("objectname");
		$this->obj_form->subforms["radius_attr"]	= array("radius_attr_about");
		
		foreach ($radius_attributes as $attribute)
		{
			$this->obj_form->subforms["radius_attr"][] = $attribute;
		}


		// mikrotik vendor specific attributes.
		if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
		{
			// define the subform
			$this->obj_form->subforms["radius_attr_mikrotik"]	= array("radius_attr_mikrotik_about");


			// fetch the attributes and draw the form options
			$radius_attributes_full		= radius_attr_mikrotik();
			$radius_attributes 		= array_keys($radius_attributes_full);

			foreach ($radius_attributes as $attribute)
			{
				$structure = NULL;
				$structure["fieldname"]		= $attribute;

				// adjust field types
				switch ($radius_attributes_full[ $attribute ])
				{
					case "string":
						$structure["type"]		= "input";
					break;

					case "int":
						$structure["type"]		= "input";
						$structure["options"]["width"]	= "100";
					break;

					case "bytes":
						$structure["type"]		= "input";
						$structure["options"]["label"]	= " bytes";
						$structure["options"]["width"]	= "100";
					break;

					case "gigaword":
						$structure["type"]		= "input";
						$structure["options"]["label"]	= " 4GB multiples (4294967296 bytes)";
						$structure["options"]["width"]	= "100";
					break;

					case "bool":
						$structure["type"]		= "checkbox";
						$structure["options"]["label"]	= "Enable/Disable";
					break;

					case "ipaddr":
						$structure["type"]		= "input";
					break;
				}


				// special overrides
				switch ($attribute)
				{
					case "Mikrotik-Wireless-Enc-Algo":
						$structure["type"]		= "dropdown";

						$structure["values"]		= array("0", "1", "2");
						$structure["translations"]["0"]	= "No Encryption";
						$structure["translations"]["1"]	= "40bit WEP";
						$structure["translations"]["2"]	= "104bit WEP";

					break;


					case "Mikrotik-Rate-Limit":
						/*
							This is not an ideal solution, the rate limit for the mikrotiks can actually
							be done in a much more powerful way with definable burst rates and other
							attributes.

							Currently we define a list of possible speeds/options.
						*/
						$structure["type"]		= "dropdown";
						$structure["options"]["width"]	= "100";
						$structure["values"]		= array("1M", "5M", "10M", "15M", "20M", "25M", "30M",
											"35M", "40M", "45M", "50M", "55M", "60M", "65M",
											"70M", "75M", "80M", "85M", "90M", "100M", 
											"1000M");
						
					break;

					default:
						// nothing todo
					break;
				}


				$this->obj_form->add_input($structure);
				$this->obj_form->subforms["radius_attr_mikrotik"][] = $attribute;
			}
		}


		// vendor specific attributes
		$structure = NULL;
		$structure["fieldname"]		= "vendor_attr_about";
		$structure["type"]		= "message";
		$structure["defaultvalue"]	= "<p>Define vendor-specific radius attributes below as either check or reply attributes in the form of &lt;radius-attribute&gt; &lt;operator&gt; &lt;value&gt;</p>";
		$this->obj_form->add_input($structure);

		$this->obj_form->subforms["radius_attr_vendor"]	= array("vendor_attr_about");



		// vendor specific replies
		$structure = NULL;
		$structure["fieldname"]		= "vendor_attr_about_reply";
		$structure["type"]		= "message";
		$structure["defaultvalue"]	= "<p>". lang_trans("vendor_attr_about_reply") ."</p>";
		$this->obj_form->add_input($structure);

		$this->obj_form->subforms["radius_attr_vendor"][] = "vendor_attr_about_reply";


		for ($i=0; $i < $this->num_vendor_fields; $i++)
		{
			$structure = NULL;
			$structure["fieldname"]		= "vendor_attr_reply_$i";
			$structure["type"]		= "input";
			$structure["options"]["width"]	= "500";
			$this->obj_form->add_input($structure);

			$this->obj_form->subforms["radius_attr_vendor"][] = "vendor_attr_reply_$i";
		}


		// vendor specific checks
		$structure = NULL;
		$structure["fieldname"]		= "vendor_attr_about_check";
		$structure["type"]		= "message";
		$structure["defaultvalue"]	= "<p>". lang_trans("vendor_attr_about_check") ."</p>";
		$this->obj_form->add_input($structure);

		$this->obj_form->subforms["radius_attr_vendor"][] = "vendor_attr_about_check";


		for ($i=0; $i < $this->num_vendor_fields; $i++)
		{
			$structure = NULL;
			$structure["fieldname"]		= "vendor_attr_check_$i";
			$structure["type"]		= "input";
			$structure["options"]["width"]	= "500";
			$this->obj_form->add_input($structure);

			$this->obj_form->subforms["radius_attr_vendor"][] = "vendor_attr_check_$i";
		}




		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_object";
		$structure["type"]		= "hidden";
		$this->obj_form->add_input($structure);
			
		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		


		// define basis subforms
		$this->obj_form->subforms["hidden"]		= array("id_object");
		$this->obj_form->subforms["submit"]		= array("submit");


	} // end of function ui_form




	/*
		ui_process

		Takes the data from ui_form, validates and generates a data structure.
	*/
	
	function ui_process()
	{
		log_write("debug", "ui_radius_attributes", "Executing ui_process()");

		$this->obj_owner->id	= security_form_input_predefined("int", "id_object", 0, "");

		$num_vendor_fields	= sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS_MAXVENDOR'");


		if (!$this->obj_owner->verify_id())
		{
			log_write("error", "process", "The entity you have attempted to edit - ". $this->obj_owner->id ." - does not exist in this system.");
		}
		else
		{
			// load existing data
			$this->obj_owner->load_data();

			// error handling stuff
			security_form_input_predefined("any", "objectname", 0, "");

			// standard radius attributes
			$radius_attributes = radius_attr_standard();

			foreach ($radius_attributes as $attribute)
			{
				// unset any current values
				$this->obj_owner->data[ $attribute ] = array();

				// fetch the new values
				$tmp = stripslashes(security_form_input_predefined("any", $attribute, 0, ""));

				if (!empty($tmp))
				{
					$this->obj_owner->data[ $attribute ] = $tmp;
				}
			}


			// vendor specific: mikrotik
			if ($GLOBALS["config"]["FEATURE_RADIUS_MIKROTIK"] == "enabled")
			{
				$radius_attributes_full	= radius_attr_mikrotik();
				$radius_attributes	= array_keys($radius_attributes_full);

				foreach ($radius_attributes as $attribute)
				{
					// unset any current values
					$this->obj_owner->data[ $attribute ] = array();


					// handle based on type
					switch ($radius_attributes_full[ $attribute] )
					{
						case "string":
							$tmp = stripslashes(security_form_input_predefined("any", $attribute, 0, ""));
						break;

						case "int":
						case "bytes":
						case "gigaword":
							$tmp = stripslashes(security_form_input_predefined("int", $attribute, 0, ""));
						break;

						case "bool":
							$tmp = stripslashes(security_form_input_predefined("checkbox", $attribute, 0, ""));
						
							// override
							$this->obj_owner->data[ $attribute ] = $tmp;
						break;
					}

					if (!empty($tmp))
					{
						$this->obj_owner->data[ $attribute ] = $tmp;
					}
		
				}
			}


			// vendor specific: generic
			$this->obj_owner->data["radiusCheckItem"] = array();
			$this->obj_owner->data["radiusReplyItem"] = array();

			for ($i=0; $i < $num_vendor_fields; $i++)
			{
				$tmp = stripslashes(security_form_input_predefined("any", "vendor_attr_check_$i", 0, ""));
				if (!empty($tmp))
				{
					$this->obj_owner->data["radiusCheckItem"][] = $tmp;
				}

				$tmp = stripslashes(security_form_input_predefined("any", "vendor_attr_reply_$i", 0, ""));
				if (!empty($tmp))
				{
					$this->obj_owner->data["radiusReplyItem"][] = $tmp;
				}
			}

		} // end if valid user ID


		// verify that the feature is currently enabled
		if (sql_get_singlevalue("SELECT value FROM config WHERE name='FEATURE_RADIUS' LIMIT 1") == "disabled")
		{
			log_write("error", "process", "Radius attribute configuration has been disabled by the administrator. Use the admin configuration to page to enable it if required.");
		}
		

		// legacy data safety check
		if (in_array("account", $this->obj_owner->data["objectclass"]))
		{
			log_write("error", "process", "This user needs to be upgraded to use inetOrgPerson before radius attributes can be changed.");
		}


	} // end of ui_process

} // end of class ui_radius_attributes





?>
