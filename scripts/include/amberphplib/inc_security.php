<?php
/*
	security.php

	Provides a number of core security functions for tasks such as verification
	of input.
*/



/*
	security_localphp ($url)

	Verifies that the provided URL is for a local PHP script, to prevent exploits
	by an attacker including a remote file, or including another file on the local
	machine in order to read it's contents.

	Success: return 1
	Failure: return 0

*/
function security_localphp($url)
{
	// does the url start with a slash? (/)
	if (preg_match("/^\//", $url))			{ return 0; }

	// does the url start with a ../?
	if (preg_match("/^\.\.\//", $url))		{ return 0; }
     
	// does the url (at any point) contain "://" (for ftp://, http://, etc)
	if (preg_match("/:\/\//", $url))		{ return 0; }

	// make sure the file is a php file!
	if (!preg_match("/.php$/", $url))		{ return 0; }

	// everything was cool
	return 1;
}




/*
	security_form_input ( $expression, $valuename, $numchars, $errormsg )

	Verifies input from $_POST[$valuename] using the regex provided
	as well as checking the length of the variable.
	
	This function has 2 important roles:
	* Preventing SQL or HTML injection of page content
	* Check user input from the form to make sure it's valid - eg: email addresses, dates, etc.

	Success:	Sets the session variable for form errors.
			Returns the value

	Failure:	Sets the session variable for form errors.
			Flags the value as being an incorrect one.
			Appends the errormessage to the errormessage value
			Returns the value.
*/
function security_form_input($expression, $valuename, $numchars, $errormsg)
{
	// get post data
	$input = $_POST[$valuename];
	

	// strip any HTML tags
	$input = strip_tags($input);

        // check if magic quotes is on or off and process the input correctly.
        //
        // this prevents SQL injections, by backslashing -- " ' ` \ -- etc.
        //
	if (get_magic_quotes_gpc() == 0)
	{
		$input = addslashes($input);
	}


	if (strlen($input) >= $numchars)
	{
		// make sure input is valid, and process accordingly.
		if (preg_match($expression, $input) || $input == "")
		{
			// valid input
			$_SESSION["error"][$valuename] = $input;
			return $input;
		}
		else
		{
			// invalid input - does not match regex

			// if there is no errormsg supplied, set a default one by looking
			// up the translation of the fieldname and reporting it.
			if ($errormsg == "")
			{
				$translation	= language_translate_string($_SESSION["user"]["lang"], $valuename);
				$errormsg	= "Invalid $translation supplied, please correct.";
			}

			// report the error
			$_SESSION["error"]["message"][] = "$errormsg";
			$_SESSION["error"]["". $valuename . "-error"] = 1;
			$_SESSION["error"][$valuename] = $input;
		}
	}
	else
	{
		// invalid input - input not long enough/no input

		// if there is no errormsg supplied, set a default one by looking
		// up the translation of the fieldname and reporting it.
		if ($errormsg == "")
		{
			$translation	= language_translate_string($_SESSION["user"]["lang"], $valuename);
			$errormsg	= "Sorry, \"$translation\" must be at least $numchars charactors.";
		}

		// report the error
		$_SESSION["error"]["message"][] = "$errormsg";
		$_SESSION["error"]["". $valuename . "-error"] = 1;
		$_SESSION["error"][$valuename] = $input;
	}

	return 0;
}

/*
	security_form_input_predefined ($type, $valuename, $numchar, $errormsg)
	
	Wrapper function for the security_form_input function with various
	pre-defined checks.

	"type" options:
	* any		Allow any input (note: HTML tags will still be stripped)
	* date		Reassembles 3 different fields into a single YYYY-MM-DD format
	* date_string	Accepts YYYY-MM-DD
	* hourmins	Take 2 fields (hours + minutes), adds them, and returns the number of seconds
	* email		Standard email address
	* int		Standard integer
	* money		0.2f floating point money value - the security function will perform padding
	* float		Floating point integer
	* ipv4		XXX.XXX.XXX.XXX IPv4 syntax
	* checkbox	Checkbox - return 1 if set, 0 if not

	For further details, refer to the commentsfor the security_form_input function.
*/
function security_form_input_predefined ($type, $valuename, $numchar, $errormsg)
{
	$expression = NULL;
	
	
	// run through the actions for each item type
	switch ($type)
	{
		case "any":
			$expression = "/^[\S\s]*$/";
		break;

		case "date":
			// TODO: audit the error handling in this function, seems like it's generating
			// messages which are used for no reason.

			// if there is no errormsg supplied, set a default one by looking
			// up the translation of the fieldname and reporting it.
			if ($errormsg == "")
			{
				$translation	= language_translate_string($_SESSION["user"]["lang"], $valuename);
				$errormsg	= "Invalid $translation supplied, please correct.";
			}

		
			// dates are a special field, since they have to be passed
			// from the form as 3 different inputs, but we want to re-assemble them
			// into a single YYYY-MM-DD format
			
			$date_dd	= intval($_POST[$valuename."_dd"]);
			$date_mm	= intval($_POST[$valuename."_mm"]);
			$date_yyyy	= intval($_POST[$valuename."_yyyy"]);

			// make sure a date has been provided
			if ($numchar)
			{
				if ($date_dd < 1 || $date_dd > 31)
					$errormsg_tmp = "Invalid date input";

				if ($date_mm < 1 || $date_mm > 12)
					$errormsg_tmp = "Invalid date input";
			
				if ($date_yyyy < 1600 || $date_yyyy > 2999)
					$errormsg_tmp = "Invalid date input";
			}
			else
			{
				// the date is not a required field, but we need to make sure any input is valid
				if ($date_dd > 31)
					$errormsg_tmp = "Invalid date input";
					
				if ($date_mm > 12)
					$errormsg_tmp = "Invalid date input";

				if ($date_yyyy > 2999)
					$errormsg_tmp = "Invalid date input";
			}

			// make sure user has filled in all 3 date fields
			if ($date_dd && (!$date_mm || !$date_yyyy))
				$errormsg_tmp = "Invalid date input";

			if ($date_mm && (!$date_dd || !$date_yyyy))
				$errormsg_tmp = "Invalid date input";
				
			if ($date_yyyy && (!$date_dd || !$date_mm))
				$errormsg_tmp = "Invalid date input";

			// pad dates
			$date_dd	= sprintf("%02d", $date_dd);
			$date_mm	= sprintf("%02d", $date_mm);
			$date_yyyy	= sprintf("%04d", $date_yyyy);
		
			// join the dates
			$date_final = "$date_yyyy-$date_mm-$date_dd";
			
			if ($errormsg_tmp)
			{
				// there has been an error - flag the hourmins field as being incorrect input
				$_SESSION["error"]["message"][] = $errormsg;
				$_SESSION["error"]["". $valuename . "-error"] = 1;
				$_SESSION["error"][$valuename] = 0;
			}
			else
			{
				// save value incase of errors
				$_SESSION["error"][$valuename] = $date_final;
			}

			
			// return the value
			return $date_final;
			
		break;

		case "hourmins":
			// hourmins is a special field - we want to take
			// two fields (hours + mins) and add then together
			// to produce the number of seconds.

			// if there is no errormsg supplied, set a default one by looking
			// up the translation of the fieldname and reporting it.
			if ($errormsg == "")
			{
				$translation	= language_translate_string($_SESSION["user"]["lang"], $valuename);
				$errormsg	= "Invalid $translation supplied, please correct.";
			}


			$time_hh	= intval($_POST[$valuename."_hh"]);
			$time_mm	= intval($_POST[$valuename."_mm"]);

			// caclulate the time in seconds
			$timestamp 	= ($time_mm * 60) + (($time_hh * 60) * 60);

			// make sure a value has been provided
			if ($numchar && $timestamp == 0)
			{
				$_SESSION["error"]["message"][] = $errormsg;
				$_SESSION["error"]["". $valuename . "-error"] = 1;
				$_SESSION["error"][$valuename] = 0;
			}
			else
			{
				$_SESSION["error"][$valuename] = $timestamp;
			}

			return $timestamp;

		break;

		case "date_string":
			$expression = "/^[0-9]*-[0-9]*-[0-9]*$/";
		break;


		case "int":
			$expression = "/^[0-9]*$/";
		break;

		case "money":

			// if there is no errormsg supplied, set a default one by looking
			// up the translation of the fieldname and reporting it.
			if ($errormsg == "")
			{
				$translation	= language_translate_string($_SESSION["user"]["lang"], $valuename);
				$errormsg	= "Invalid $translation supplied, please correct.";
			}

			// verify as a floating point number
			$expression = "/^[0-9]*.[0-9]*$/";
			$value = security_form_input($expression, $valuename, $numchar, $errormsg);

			// perform padding
			if ($value != "error")
			{
				$value = sprintf("%0.2f", $value);
			}

			// trigger error if value is 0.00
			if ($numchar && $value == "0.00")
			{
				$_SESSION["error"]["message"][]			= $errormsg;
				$_SESSION["error"]["". $valuename . "-error"]	= 1;
				$_SESSION["error"][$valuename]			= 0;
			}

			return $value;
			
		break;

		case "float":

			// value could be a float, or an integer - we need to check for either
			if (preg_match("/^[0-9]*$/", $_POST[$valuename]))
			{
				// is an int
				$expression = "/^[0-9]*$/";
			}
			else
			{
				// either float or invalid - run check for int
				$expression = "/^[0-9]*.[0-9]*$/";
			}

		break;
		
		case "email":
			$expression = "/^([A-Za-z0-9._-])+\@(([A-Za-z0-9-])+\.)+([A-Za-z0-9])+$/";
		break;

		case "ipv4":
			$expression = "/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/";
		break;

		case "checkbox":
			if ($_POST[$valuename])
			{
				return 1;
			}
			else
			{
				return 0;
			}
		break;

		default:
			print "Warning: No such security check for type $type<br>";
			$expression = "/^[\S\s]*$/";
		break;

	}

	return security_form_input($expression, $valuename, $numchar, $errormsg);
}


/*
	security_script_input ($expression, $value)

	Checks data that gets provided to a script (eg: returned error messages,
	get commands, etc). If data passes, it gets returned. If it doesn't NULL
	is returned, and the value is set to "error".
	
	Success: Returns the value.
	Failure: Returns "error".
*/
function security_script_input ($expression, $value)
{
	// if the input matches the regex, all is good, otherwise set to "error".
	if (preg_match($expression, $value))
	{
	        // check if magic quotes is on or off and process the input correctly.
	        //
	        // this prevents SQL injections, by backslashing -- " ' ` \ -- etc.
	        //
		if (get_magic_quotes_gpc() == 0)
		{
			$value = addslashes($value);
		}

		return $value;
	}
	else
	{
		return "error";
	}		
}



/*
	security_script_predefined ($value)
	
	Wrapper function for the security_script_input function with various
	pre-defined checks. This function is simular in to security_form_predefined but
	is simpler since it doesn't have all the complex error handling code

	"type" options:
	* any		Allow any input (note: HTML tags will still be stripped)
	* date		Only permit YYYY-MM-DD format
	* hourmins	Convert hours:minutes time format into seconds
	* email		Standard email address
	* int		Standard integer
	* money		0.2f floating point money value - the security function will perform padding
	* float		Floating point integer
	* ipv4		XXX.XXX.XXX.XXX IPv4 syntax

	For further details, refer to the commentsfor the security_form_input function.
*/
function security_script_input_predefined ($type, $value)
{
	$expression = NULL;

	// don't bother processing empty variables, just return blank
	if ($value == "")
	{
		return $value;
	}

	// run through the actions for each item type and work out an expression to use
	switch ($type)
	{
		case "any":
			$expression = "/^[\S\s]*$/";
		break;

		case "date":
			$expression = "/^[0-9]{4}-[0-9]*-[0-9]*$/";
		break;

		case "hourmins":
			// hourmins is a special field - we want to take
			// two fields (hours + mins) and add then together
			// to produce the number of seconds.

			// calculate the time in seconds
			$time	= explode(":", $value);
			$value	= ($time[1] * 60) + (($time[0] * 60) * 60);

			$expression = "/^[0-9]*$/";
		break;

		case "int":
			$expression = "/^[0-9]*$/";
		break;

		case "money":
			$expression = "/^[0-9]*.[0-9]*$/";

			// perform padding
			$value = sprintf("%0.2f", $value);
		break;

		case "float":

			// value could be a float, or an integer - we need to check for either
			if (preg_match("/^[0-9]*$/", $value))
			{
				// is an int
				$expression = "/^[0-9]*$/";
			}
			else
			{
				// either float or invalid - run check for int
				$expression = "/^[0-9]*.[0-9]*$/";
			}

		break;
		
		case "email":
			$expression = "/^([A-Za-z0-9._-])+\@(([A-Za-z0-9-])+\.)+([A-Za-z0-9])+$/";
		break;

		case "ipv4":
			$expression = "/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/";
		break;

		default:
			print "Warning: No such security check for type $type<br>";
			$expression = "/^[\S\s]*$/";
		break;

	}

	return @security_script_input($expression, $value);
}




?>
