<?php
/*
	misc.php
	
	Various one-off functions
*/



/*
	CONFIGURATION FUNCTIONS 

	Configuration functions perform queries against the config DB with the structure of:
	
	CREATE TABLE `config` (
	  `name` varchar(255) NOT NULL default '',
	  `value` varchar(255) NOT NULL default '',
	  PRIMARY KEY  (`name`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;
*/


/*
	config_generate_uniqueid()

	This function will generate a unique ID by looking up the current value of the supplied
	name from the config database, and will then work out an avaliable value.

	Once a suitable value has been determined, the code will return it and then update the 
	value in the config table.

	This function is ideal for when you need a field to be auto-incremented, but still providing
	the user the ability to over-write it with their own value.

	Values
	config_name	Name of the configuration field to fetch the value from
	check_sql	(optional) SQL query to check for current usage of this ID. Note that the VALUE keyword will
			be replaced by the code ID.
				eg: "SELECT id FROM mytable WHERE codevalue='VALUE'

	Returns
	#	unique ID to be used.
*/
function config_generate_uniqueid($config_name, $check_sql)
{
	log_debug("inc_misc", "Executing config_generate_uniqueid($config_name)");
	
	$config_name = strtoupper($config_name);
	
	$returnvalue	= 0;
	$uniqueid	= 0;
	

	// fetch the starting ID from the config DB
	$uniqueid	= sql_get_singlevalue("SELECT value FROM config WHERE name='$config_name'");

	if (!$uniqueid)
		die("Unable to fetch $config_name value from config database");


	if ($check_sql)
	{
		// we will use the supplied SQL query to make sure this value is not currently used
		while ($returnvalue == 0)
		{
			$sql_obj		= New sql_query;
			$sql_obj->string	= str_replace("VALUE", $uniqueid, $check_sql);
			$sql_obj->execute();

			if ($sql_obj->num_rows())
			{
				// the ID has already been used, try incrementing
				$uniqueid++;
			}
			else
			{
				// found an avaliable ID
				$returnvalue = $uniqueid;
			}
		}
	}
	else
	{
		// conducting no DB checks.
		$returnvalue = $uniqueid;
	}
	

	// update the DB with the new value + 1
	$uniqueid++;
				
	$sql_obj		= New sql_query;
	$sql_obj->string	= "UPDATE config SET value='$uniqueid' WHERE name='$config_name'";
	$sql_obj->execute();


	return $returnvalue;
}






/* FORMATTING/DISPLAY FUNCTIONS */


/*
	format_file_extension

	Returns only the extension portion of the supplied filename/filepath.

	Values
	filename	Filename or path

	Returns
	string		file extension (lowercase)
*/
function format_file_extension($filename)
{
	log_debug("misc", "Executing format_file_extension($filename)");

	return strtolower(substr(strrchr($filename,"."),1));
}


/*
	format_file_name

	Returns the filename & extension of the supplied filepath - effectively strips
	off the directory path.

	Values
	filepath	File path

	Returns
	string		filename
*/
function format_file_name($filepath)
{
	log_debug("misc", "Executing format_file_name($filepath)");

	return substr(strrchr($filepath,"/"),1);
}



/*
	format_text_display($text)

	Formats a block of text from a database into a form suitable for display as HTML.

	Returns the processed text.
*/
function format_text_display($text)
{
	log_debug("misc", "Executing format_text_display(TEXT)");
	
	// replace unrenderable html tags of > and <
	$text = str_replace(">", "&gt;", $text);
	$text = str_replace("<", "&lt;", $text);
	
	// fix newlines last
	$text = str_replace("\n", "<br>", $text);

	return $text;
}


/*
	format_text_textarea($text)

	Formats a block of text from a database into a form suitable for display inside textarea forms.

	Returns the processed text.
*/
function format_text_textarea($text)
{
	log_debug("misc", "Executing format_text_textarea(TEXT)");
	
	// replace unrenderable html tags of > and <
	$text = str_replace(">", "&gt;", $text);
	$text = str_replace("<", "&lt;", $text);
	
	return $text;
}



/*
	format_size_human($bytes)

	Returns a human readable size.
*/
function format_size_human($bytes)
{
	log_debug("misc", "Executing format_size_human($bytes)");

	if(!$bytes)
	{
		// unknown - most likely the program hasn't called one one of the fetch_information_by_* functions first.
		log_debug("misc", "Error: Unable to determine file size - no value provided");	
		return "unknown size";
	}
	else
	{
		$file_size_types = array(" Bytes", " KB", " MB", " GB", " TB");
		return round($bytes/pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $file_size_types[$i];
	}
}

/*
	format_size_bytes($string)

	Converts a human readable size string to bytes.
*/
function format_size_bytes($string)
{
	log_debug("misc", "Executing format_size_bytes($string)");

	if(!$string)
	{
		// unknown - most likely the program hasn't called one one of the fetch_information_by_* functions first.
		log_debug("misc", "Error: Unable to determine file size - no value provided");	
		return "unknown size";
	}
	else
	{
		$string	= strtolower($string);
		$string = preg_replace("/\s*/", "", $string);			// strip spaces
		$string = preg_replace("/,/", "", $string);			// strip formatting
		$string = preg_match("/^([0-9]*)([a-z]*)$/", $string, $values);

		if ($values[2])
		{
			switch ($values[2])
			{
		        	case "g":
				case "gb":
					$bytes = (($values[1] * 1024) * 1024) * 1024;
				break;

				case "m":
				case "mb":
					$bytes = ($values[1] * 1024) * 1024;
				break;

				case "k":
				case "kb":
					$bytes = $values[1] * 1024;
				break;

				case "b":
				case "bytes":
				default:
					$bytes = int($values[1]);
				break;
			}
		}
		else
		{
			// assume value must be in bytes already.
			$bytes = int($values[1]);
		}

		return $bytes;
	}
}




/*
	format_msgbox($type, $text)

	Creates a coloured message box, based on the type.

	Supported types:
	important
	info
*/
function format_msgbox($type, $text)
{
	log_debug("misc", "Executing format_msgbox($type, text)");

	print "<table width=\"100%\" class=\"table_highlight_$type\">";
	print "<tr>";
		print "<td>";
		print "$text";
		print "</td>";
	print "</tr>";
	print "</table>";
}

/*
	format_linkbox($type, $hyperlink, $text)

	Creates a coloured message box configured to take the user
	to the specified link upon being clicked

	Supported types:
	important
	info
*/
function format_linkbox($type, $hyperlink, $text)
{
	log_debug("misc", "Executing format_linkbox($type, $hyperlink, text)");

	print "<table width=\"100%\" class=\"table_linkbox_$type\" onclick=\"location.href='$hyperlink'\">";
	print "<tr>";
		print "<td>";
		print "$text";
		print "</td>";
	print "</tr>";
	print "</table>";
}



/*
	format_money($amount)

	Formats the provided floating integer and adds the default currency and applies
	rounding to it to make a number suitable for display.

	Set nocurrency to 1 to disable addition of the currency symbol
*/
function format_money($amount, $nocurrency = NULL)
{
	log_debug("misc", "Executing format_money($amount)");

	// 2 decimal places
	$amount = sprintf("%0.2f", $amount);

	// formatting for readability
	$amount = number_format($amount, "2", ".", ",");


	if ($nocurrency)
	{
		return $amount;
	}
	else
	{
		// add currency & return
		$position = sql_get_singlevalue("SELECT value FROM config WHERE name='CURRENCY_DEFAULT_SYMBOL_POSITION'");

		if ($position == "after")
		{
			$result = "$amount ". sql_get_singlevalue("SELECT value FROM config WHERE name='CURRENCY_DEFAULT_SYMBOL'");
		}
		else
		{
			$result = sql_get_singlevalue("SELECT value FROM config WHERE name='CURRENCY_DEFAULT_SYMBOL'") ."$amount";
		}

		return $result;
	}
}


/*
	format_arraytocommastring($array)

	returns a provided array as a comma seporated string - very useful for creating value
	lists to be used in a SQL query.
*/
function format_arraytocommastring($array)
{
	log_debug("misc", "Executing format_arraytocommastring(Array)");

	$returnstring = "";

	$array_num = count($array);

	for ($i=0; $i < $array_num; $i++)
	{
		$returnstring .= $array[$i];

		if ($i != ($array_num - 1))
		{
			$returnstring .= ", ";
		}
	}

	return $returnstring;
}



/* TIME FUNCTION */


/*
	time_date_to_timestamp($date)

	returns a timestamp calculated from the provided YYYY-MM-DD date
*/
function time_date_to_timestamp($date)
{
	log_debug("misc", "Executing time_date_to_timestamp($date)");


	if ($date == "0000-00-00")
	{
		// feeding 0000-00-00 to mktime would cause an incorrect timedstamp to be generated
		return 0;
	}
	else
	{
		$date_a = explode("-", $date);

		return mktime(0, 0, 0, $date_a[1], $date_a[2] , $date_a[0]);
	}
}


/*
	time_format_hourmins($seconds)
	
	returns the number of hours, and the number of minutes in the form of H:MM
*/
function time_format_hourmins($seconds)
{
	log_debug("misc", "Executing time_format_hourmins($seconds)");
	
 	$minutes	= $seconds / 60;
	$hours		= sprintf("%d",$minutes / 60);

	$excess_minutes = sprintf("%02d", $minutes - ($hours * 60));


	// excess minutes must never be negative
	if ($excess_minutes < 0)
	{
		$excess_minutes = $excess_minutes * -1;
	}

	return "$hours:$excess_minutes";
}


/*
	time_format_humandate

	Provides a date formated in the user's perferred way. If no date is provided, will return the current date.

	Values
	date		Format YYYY-MM-DD OR unix timestamp (optional)

	Returns
	string		Date in human-readable format.
*/
function time_format_humandate($date = NULL)
{
	log_debug("misc", "Executing time_format_humandate($date)");

	if ($date)
	{
		if (is_int($date))
		{
			// already a timestamp, yay!
			$timestamp = $date;
		}
		else
		{
			// convert date to timestamp so we can work with it
			$timestamp = time_date_to_timestamp($date);
		}
	}
	else
	{
		// no date supplied - generate current timestamp
		$timestamp = mktime();
	}


	if ($_SESSION["user"]["dateformat"])
	{
		// fetch from user preferences
		$format = $_SESSION["user"]["dateformat"];
	}
	else
	{
		// user hasn't chosen a default time format yet - use the system
		// default
		$format = sql_get_singlevalue("SELECT value FROM config WHERE name='DATEFORMAT' LIMIT 1");
	}


	// convert to human readable format
	switch ($format)
	{
		case "mm-dd-yyyy":
			return date("m-d-Y", $timestamp);
		break;

		case "dd-mm-yyyy":
			return date("d-m-Y", $timestamp);
		break;
		
		case "yyyy-mm-dd":
		default:
			return date("Y-m-d", $timestamp);
		break;
	}
}


/*
	time_calculate_weekstart($date_selected_weekofyear, $date_selected_year)

	returns the start date of the week in format YYYY-MM-DD
	
*/
function time_calculate_weekstart($date_selected_weekofyear, $date_selected_year)
{
	log_debug("misc", "Executing time_calculate_weekstart($date_selected_weekofyear, $date_selected_year)");
	
	// work out the start date of the current week
	$date_curr_weekofyear	= date("W");
	$date_curr_year		= date("Y");
	$date_curr_start	= mktime(0, 0, 0, date("m"), ((date("d") - date("w")) + 1) , $date_curr_year);

	// work out the difference in the number of weeks desired
	$date_selected_weekdiff	= ($date_curr_year - $date_selected_year) * 52;
	$date_selected_weekdiff += ($date_curr_weekofyear - $date_selected_weekofyear);

	// work out the difference in seconds (1 week == 604800 seconds)
	$date_selected_seconddiff = $date_selected_weekdiff * 604800;

	// timestamp of the first day in the week.
	$date_selected_start = $date_curr_start - $date_selected_seconddiff;

	return date("Y-m-d", $date_selected_start);
}


/*
	time_calculate_daysofweek($date_selected_start_ts)

	Passing YYYY-MM-DD of the first day of the week will
	return an array containing date of each day in YYYY-MM-DD format
*/
function time_calculate_daysofweek($date_selected_start)
{
	log_debug("misc", "Executing time_calculate_daysofweek($date_selected_start)");

	$days = array();

	// get the start day, month + year
	$dates = explode("-", $date_selected_start);
	
	// get the value for all the days
	for ($i=0; $i < 7; $i++)
	{
		$days[$i] = date("Y-m-d", mktime(0,0,0,$dates[1], ($dates[2] + $i), $dates[0]));
	}

	return $days;
}


/*
	time_calculate_daynum($date)

	Calculates what day the supplied date is in. If not date is supplied, then
	returns the current day.
*/
function time_calculate_daynum($date = NULL)
{
	log_debug("misc", "Executing time_calculate_daynum($date)");

	if (!$date)
	{
		return date("d");
	}
	else
	{
		preg_match("/-([0-9]*)$/", $date, $matches);

		return $matches[1];
	}
}



/*
	time_calculate_weeknum($date)

	Calculates what week the supplied date is in. If not date is supplied, then
	returns the current week.
*/
function time_calculate_weeknum($date = NULL)
{
	log_debug("misc", "Executing time_calculate_weeknum($date)");

	if (!$date)
	{
		$date = date("Y-m-d");
	}


	/*
		Use the SQL database to get the week number based on ISO 8601
		selection criteria.

		Note that we intentionally use SQL instead of the php date("W") function, since
		in testing the date("W") function has been found to beinconsistant on different systems.

		TODO: Investigate further what is wrong with PHP date("W")
	*/
	return sql_get_singlevalue("SELECT WEEK('$date',1) as value");
}


/*
	time_calculate_monthnum($date)

	Calculates what month the supplied date is in. If not date is supplied, then
	returns the current month.
*/
function time_calculate_monthnum($date = NULL)
{
	log_debug("misc", "Executing time_calculate_monthnum($date)");

	if (!$date)
	{
		return date("m");
	}
	else
	{
		preg_match("/^[0-9]*-([0-9]*)-/", $date, $matches);

		return $matches[1];
	}
}



/*
	time_calculate_yearnum($date)

	Calculates what year the supplied date is in. If not date is supplied, then
	returns the current year.
*/
function time_calculate_yearnum($date = NULL)
{
	log_debug("misc", "Executing time_calculate_yearnum($date)");

	if (!$date)
	{
		return date("Y");
	}
	else
	{
		preg_match("/^([0-9]*)-/", $date, $matches);

		return $matches[1];
	}
}



/*
	time_calculate_monthday_first($date)

	Calculates what the first date of the month is, for the provided date. If
	no date is provided, returns for the current month.
*/
function time_calculate_monthdate_first($date = NULL)
{
	log_debug("misc", "Executing time_calculate_monthday_first($date)");

	if (!$date)
	{
		$date = date("Y-m-d");
	}

	$date = preg_replace("/-[0-9]*$/", "-01", $date);

	return $date;
}
	

/*
	time_calculate_monthday_last($date)

	Calculates what the final date of the month is, for the provided date. If
	no date is provided, returns for the current month.
*/
function time_calculate_monthdate_last($date = NULL)
{
	log_debug("misc", "Executing time_calculate_monthday_last($date)");

	if (!$date)
	{
		$timestamp	= mktime();
		$date		= date("Y-m-d", $timestamp);
	}
	else
	{
		$timestamp = time_date_to_timestamp($date);
	}
	
	// fetch the final day of the month
	$lastday = date("t", $timestamp);
	
	// replace the day with the final day
	$date = preg_replace("/-[0-9]*$/", "-$lastday", $date);

	// done
	return $date;
}
	




/* HELP FUNCTIONS */

/*
	helplink( id )
	returns an html string, including a help icon, with a hyperlink to the help page specified by id.
*/

function helplink($id)
{
	return "<a href=\"javascript:url_new_window_minimal('help/viewer.php?id=$id');\" title=\"Click here for a popup help box\"><img src=\"images/icons/help.gif\" alt=\"?\" border=\"0\"></a>";
}




/* LOGGING FUNCTIONS */


/*
	log_error_render()

	Displays any error logs
*/
function log_error_render()
{
        if ($_SESSION["error"]["message"])
        {
		print "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">";
                print "<tr><td bgcolor=\"#ffeda4\" style=\"border: 1px dashed #dc6d00; padding: 3px;\">";
                print "<p><b>Error:</b><br><br>";

		foreach ($_SESSION["error"]["message"] as $errormsg)
		{
			print "$errormsg<br>";
		}
		
		print "</p>";
                print "</td></tr>";
		print "</table>";
	}
}


/*
	log_notification_render()

	Displays any notification messages, provided that there are no error messages as well
*/
function log_notification_render()
{
        if (isset($_SESSION["notification"]["message"]) && !isset($_SESSION["error"]["message"]))
        {
		print "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">";
                print "<tr><td bgcolor=\"#c7e8ed\" style=\"border: 1px dashed #374893; padding: 3px;\">";
                print "<p><b>Notification:</b><br><br>";
		
		foreach ($_SESSION["notification"]["message"] as $notificationmsg)
		{
			print "$notificationmsg<br>";
		}

		print "</p>";
                print "</td></tr>";
		print "</table>";
        }
}




/*
	log_debug_render()

	Displays the debugging log
*/
function log_debug_render()
{
	log_debug("inc_misc", "Executing log_debug_render()");


	print "<p><b>Debug Output:</b></p>";
	print "<p><i>Please be aware that debugging will cause some impact on performance and should be turned off in production.</i></p>";
	
	
	// table header
	print "<table class=\"table_content\" width=\"100%\" cellspacing=\"0\">";
	
	print "<tr class=\"header\">";
		print "<td nowrap><b>Time</b></td>";
		print "<td nowrap><b>Memory</b></td>";
		print "<td nowrap><b>Type</b></td>";
		print "<td nowrap><b>Category</b></td>";
		print "<td><b>Message/Content</b></td>";
	print "</tr>";

	// get first time entry
	$time_first = (float)$_SESSION["user"]["log_debug"][0]["time_sec"] + (float)$_SESSION["user"]["log_debug"][0]["time_usec"];

	// count SQL queries
	$num_sql_queries = 0;

	// content
	foreach ($_SESSION["user"]["log_debug"] as $log_record)
	{
		// get last time entry
		$time_last = (float)$log_record["time_sec"] + (float)$log_record["time_usec"];


		// choose formatting
		switch ($log_record["type"])
		{
			case "error":
				print "<tr bgcolor=\"#ff5a00\">";
			break;

			case "warning":
				print "<tr bgcolor=\"#ffeb68\">";
			break;

			case "sql":
				print "<tr bgcolor=\"#7bbfff\">";
				$num_sql_queries++;
			break;

			default:
				print "<tr>";
			break;
		}
		
		// display
		print "<td nowrap>". $time_last  ."</td>";
		print "<td nowrap>". format_size_human($log_record["memory"]) ."</td>";
		print "<td nowrap>". $log_record["type"] ."</td>";
		print "<td nowrap>". $log_record["category"] ."</td>";
		print "<td>". $log_record["content"] ."</td>";
		print "</tr>";


	}

	print "</table>";


	// report completion time
	$time_diff = ($time_last - $time_first);

	print "<p>Completed in $time_diff seconds.</p>";

	// report number of SQL queries
	print "<p>Executed $num_sql_queries of SQL queries</p>";
}


/*
	FILESYSTEM FUNCTIONS
*/


/*
	file_generate_name

	Generates a unique name based on the base name provided and touches it to reserve it.
	
	File permissions are 660, limiting access to webserver user for security reasons.

	Fields
	basename		Base of the filename
	extension		Extension for the file (if any)

	Returns
	string			Name for an avaliable file
*/
function file_generate_name($basename, $extension = NULL)
{
	log_debug("inc_misc", "Executing file_generate_name($basename, $extension)");
	

	if ($extension)
	{
		$extension = ".$extension";
	}

	// calculate a temporary filename
	$uniqueid = 0;
	while ($complete == "")
	{
		$filename = $basename ."_". mktime() ."_$uniqueid" . $extension;

		if (file_exists($filename))
		{
			// the filename has already been used, try incrementing
			$uniqueid++;
		}
		else
		{
			// found an avaliable ID
			touch($filename);
			chmod($filename, 0660);		// note: what happens on windows?
			return $filename;
		}
	}
}



/*
	file_generate_tmpfile

	Generates a tempory file and returns the full path & filename - files do
	not automatically get deleted, unless the temp dir is subject to an external
	process such as tmpwatch.

	Returns
	string		tmp filename & path
*/
function file_generate_tmpfile()
{
	log_debug("inc_misc", "Executing file_generate_tmpfile()");

	$path_tmpdir = sql_get_singlevalue("SELECT value FROM config WHERE name='PATH_TMPDIR'");

	return file_generate_name("$path_tmpdir/temporary_file");
}



/*
	HTTP/HEADER FUNCTIONS
*/


/*
	http_header_lookup

	Returns the full HTTP header string for the specified return code

	Fields
	num		number of the HTTP code to return

	Returns
	string		HTTP header string
*/

function http_header_lookup($num)
{
	log_debug("inc_misc", "Executing http_header_lookup($num)");

	$return_codes = array (
		100 => "HTTP/1.1 100 Continue",
		101 => "HTTP/1.1 101 Switching Protocols",
		200 => "HTTP/1.1 200 OK",
		201 => "HTTP/1.1 201 Created",
		202 => "HTTP/1.1 202 Accepted",
		203 => "HTTP/1.1 203 Non-Authoritative Information",
		204 => "HTTP/1.1 204 No Content",
		205 => "HTTP/1.1 205 Reset Content",
		206 => "HTTP/1.1 206 Partial Content",
		300 => "HTTP/1.1 300 Multiple Choices",
		301 => "HTTP/1.1 301 Moved Permanently",
		302 => "HTTP/1.1 302 Found",
		303 => "HTTP/1.1 303 See Other",
		304 => "HTTP/1.1 304 Not Modified",
		305 => "HTTP/1.1 305 Use Proxy",
		307 => "HTTP/1.1 307 Temporary Redirect",
		400 => "HTTP/1.1 400 Bad Request",
		401 => "HTTP/1.1 401 Unauthorized",
		402 => "HTTP/1.1 402 Payment Required",
		403 => "HTTP/1.1 403 Forbidden",
		404 => "HTTP/1.1 404 Not Found",
		405 => "HTTP/1.1 405 Method Not Allowed",
		406 => "HTTP/1.1 406 Not Acceptable",
		407 => "HTTP/1.1 407 Proxy Authentication Required",
		408 => "HTTP/1.1 408 Request Time-out",
		409 => "HTTP/1.1 409 Conflict",
		410 => "HTTP/1.1 410 Gone",
		411 => "HTTP/1.1 411 Length Required",
		412 => "HTTP/1.1 412 Precondition Failed",
		413 => "HTTP/1.1 413 Request Entity Too Large",
		414 => "HTTP/1.1 414 Request-URI Too Large",
		415 => "HTTP/1.1 415 Unsupported Media Type",
		416 => "HTTP/1.1 416 Requested range not satisfiable",
		417 => "HTTP/1.1 417 Expectation Failed",
		500 => "HTTP/1.1 500 Internal Server Error",
		501 => "HTTP/1.1 501 Not Implemented",
		502 => "HTTP/1.1 502 Bad Gateway",
		503 => "HTTP/1.1 503 Service Unavailable",
		504 => "HTTP/1.1 504 Gateway Time-out"       
	);

	return $return_codes[$num];
}




/*
	dir_generate_name

	Generates a unique directory based on the base name provided and creates it.
	
	Dir permissions are 770, limiting access to webserver user for security reasons.

	Fields
	basename		Base of the directory name,

	Returns
	string			Name of the directory
*/
function dir_generate_name($basename)
{
	log_debug("inc_misc", "Executing dir_generate_name($basename)");
	

	// calculate a temporary directory name
	$uniqueid = 0;
	while ($complete == "")
	{
		$dirname = $basename ."_". mktime() ."_$uniqueid";

		if (file_exists($dirname))
		{
			// the dirname has already been used, try incrementing
			$uniqueid++;
		}
		else
		{
			// found an avaliable ID
			mkdir($dirname);
			chmod($dirname, 0770);		// note: what happens on windows?
			return $dirname;
		}
	}
}


/*
	dir_generate_tmpdir

	Generates a tempory directory and returns the full path - directories do
	not automatically get deleted, unless the temp dir is subject to an external
	process such as tmpwatch.

	Returns
	string		directory path
*/
function dir_generate_tmpdir()
{
	log_debug("inc_misc", "Executing dir_generate_tmpfile()");

	$path_tmpdir = sql_get_singlevalue("SELECT value FROM config WHERE name='PATH_TMPDIR'");

	return dir_generate_name("$path_tmpdir/temporary_dir");
}


/*
	dir_list_contents

	Returns an array listing all files (recursively) in the selected directory.

	Values
	directory	(optional) defaults to current dir

	Returns
	0		failure
	array		list of directories

*/
function dir_list_contents($directory='.')
{
	log_debug("inc_misc", "Executing dir_list_contents($directory)");

	 $files = array();

	  if (is_dir($directory))
	  {
		$fh = opendir($directory);

		// loop through files
		while (($file = readdir($fh)) !== false)
		{
			if ($file != "." && $file != "..")
			{
				$filepath = $directory . '/' . $file;

				array_push($files, $filepath);
				
				if ( is_dir($filepath) )
				{
					$files = array_merge($files, dir_list_contents($filepath));
				}
			}
		}

		closedir($fh);
	}
	else
	{
		log_write("error", "inc_misc", "Invalid/non-existant directory supplied");
		return 0;
	}

	return $files;
}

?>
