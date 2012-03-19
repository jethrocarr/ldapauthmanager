<?php
/*
	ldapauthmanager_logpush


	Connects to ldapauthmanager and then tails the log file for ldap and posts
	any new log messages back to ldapauthmanager.

	This solution is better than trying to use FIFO pipes since a hang of the
	logging process will not impact ldap in any way, nor will a restart of ldap
	affect the logger.


	Copyright (c) 2010 Amberdms Ltd

	Licensed under the GNU AGPL.
*/



/*
	CONFIGURATION
*/

require("include/config.php");
require("include/amberphplib/main.php");




/*
	VERIFY LOG FILE ACCESS
*/

if (!is_readable($GLOBALS["config"]["log_file"]))
{
	log_write("error", "script", "Unable to read log file ". $GLOBALS["config"]["log_file"] ."");
	die("Fatal Error");
}



/*
	CHECK LOCK FILE

	We use exclusive file locks in non-blocking mode in order to check whether or not the script is already
	running to prevent any duplicate instances of it.

	The lock uses a file, but the file isn't actually the decider of the lock - so if the script is killed and
	doesn't properly clean up the lock file, it won't prevent the script starting again, as the actual lock
	determination is done using flock()
*/

if (empty($GLOBALS["config"]["lock_file"]))
{
	$GLOBALS["config"]["lock_file"] = "/var/lock/ldapauthmanager_lock_logpush";
}
else
{
	$GLOBALS["config"]["lock_file"] = $GLOBALS["config"]["lock_file"] ."_logpush";
}

if (!file_exists($GLOBALS["config"]["lock_file"]))
{
	touch($GLOBALS["config"]["lock_file"]);
}

$fh_lock = fopen($GLOBALS["config"]["lock_file"], "r+");

if (flock($fh_lock, LOCK_EX | LOCK_NB))
{
	log_write("debug", "script", "Obtained filelock");
}
else
{
	// One problem with the locking, is that when another script has been terminated, the lock stays active until the 
	// tail process terminates - this is due to the way PHP and fgets blocks. To nicely handle this, where there is a lock
	// issue, we log a warning about it and then wait for the lock to become available.
	//
	// If this script is terminated whilst waiting, then it will cleanly exit. If the other script is terminated or ends naturally
	// will take over and continue with logging.
	//
	// See https://projects.amberdms.com/p/oss-namedmanager/issues/341/ for more reading.
	//
	log_write("warning", "script", "Unable to execute script due to active lock file ". $GLOBALS["config"]["lock_file"] .", is another instance running?");
	log_write("warning", "script", "Waiting pending availability of log file.....");
	
	// we now wait until the lock is available
	flock($fh_lock, LOCK_EX);
}


// Establish lockfile deconstructor - this is purely for a tidy up process, the file's existance doesn't actually
// determine the lock.
function lockfile_remove()
{
	// delete lock file
	if (!unlink($GLOBALS["config"]["lock_file"]))
	{
		log_write("error", "script", "Unable to remove lock file ". $GLOBALS["config"]["lock_file"] ."");
	}
}

register_shutdown_function('lockfile_remove');






/*
	LOG PUSH CLASS

	We have a class here for handling the actual logging, it's smart enough to re-authenticate if the session
	gets terminated without dropping log messages.

	(sessions could get terminated if remote API server reboots, connection times out, no logs get generated for long
	time periods, etc)
*/


class ldapauthmanager_log_main
{
	var $client;


	/*
		authenticate

		Connects to the ldapauthmanager API and authenticates the ldap server

		Returns
		0		Failure
		1		Success
	*/
	function authenticate()
	{
		log_write("debug", "log_push", "Executing authenticate()");

		/*
			Initiate connection & authenticate to ldapauthmanager

		*/
		$this->client = new SoapClient($GLOBALS["config"]["api_url"] ."/ldapauthmanager.wsdl");
		$this->client->__setLocation($GLOBALS["config"]["api_url"] ."/ldapauthmanager.php");


		// login & get PHP session ID
		try
		{
			log_write("debug", "script", "Authenticating with API as ldap server ". $GLOBALS["config"]["api_server_name"] ."...");

			if ($this->client->authenticate($GLOBALS["config"]["api_server_name"], $GLOBALS["config"]["api_auth_key"]))
			{
				log_write("debug", "script", "Authentication successful");

				return 1;
			}

		}
		catch (SoapFault $exception)
		{
			if ($exception->getMessage() == "ACCESS_DENIED")
			{
				log_write("error", "script", "Unable to authenticate with ldapauthmanager API - check that auth API key and server name are valid");
				die("Fatal Error");
			}
			else
			{	
				log_write("error", "script", "Unknown failure whilst attempting to authenticate with the API - ". $exception->getMessage() ."");
				die("Fatal Error");
			}
		}
	}


	/*
		log_push

		Send a log message to the server

		Fields
		timestamp		UNIX timestamp
		log_type		Category of logs
		log_contents		Log contents

		Results
		0			Failure
		1			Success
	*/
	function log_push($timestamp, $log_type, $log_contents)
	{
		log_write("debug", "script", "Executing log_push(timestamp, log_type, log_contents)");

		try
		{
			$this->client->log_write($timestamp, $log_type, $log_contents);
		}
		catch (SoapFault $exception)
		{
                        if ($exception->getMessage() == "ACCESS_DENIED")
                        {
                                // no longer able to access API - perhaps the session has timed out?
                                if ($this->authenticate())
                                {
                                        $this->client->log_write($timestamp, $log_type, $log_contents);
                                }
                                else
                                {
					// this situation would only occur if the session was logged out and authentication then refused, a network
					// disruption will not lead to this occuring, since an ACCESS_DENIED response is required.

                                        log_write("error", "script", "Unable to re-establish connection with LDAPAuthManager");
                                        die("Fatal Error");
                                }
                        }
                        elseif ($exception->getMessage() == "Could not connect to host")
                        {
                                // this can happen when restarting the server or apache.... sleep for 10 seconds and retry.
                                log_write("error", "script", "Unable to connect to the host, probably apache/LDAPAuthManager outage or server unreachable");

                                sleep(10);

                        }
			elseif ($exception->getMessage() == "Error Fetching http headers")
			{
				// unexpected HTTP header issue
				log_write("error", "script", "An unexpected issue occured when fetching HTTP headers. Possible network or service disruption.");

				sleep (10);
			}
                        else
                        {
				// for any other errors, report fault and retry later - better to keep retrying and only quit if there's an actual
				// known un-fixable problem.

                                log_write("error", "script", "Unknown failure whilst attempting to push log messages - ". $exception->getMessage() ."");

				sleep(10);
                        }
		}
	}


	/*
		log_watch

		Use tail to track the file and push any new log messages to ldapauthmanager
	*/
	function log_watch()
	{
		while (true)
		{
			// we have a while here to handle the unexpected termination of the tail command
			// by restarting a new connection

			$handle = popen("tail -f ". $GLOBALS["config"]["log_file"] ." 2>&1", 'r');

			while(!feof($handle))
			{
				$buffer = fgets($handle);

				// process the log input
				//
				// example format: Jul 31 16:28:00 localhost slapd[18405]: conn=2 fd=13 ACCEPT from IP=127.0.0.1:53929 (IP=0.0.0.0:389) 
				//
				if (preg_match("/^\S*\s*\S*\s*\S*:\S*:\S*\s(\S*)\s*slapd\S*:\s([\S\s]*)$/", $buffer, $matches))
				{
					$this->log_push(time(), "slapd", $matches[2]);
				
					log_write("debug", "script", "Log Recieved: $buffer");
				}
				else
				{
					log_write("debug", "script", "Unprocessable: $buffer");
				}
			}

			pclose($handle);
		}
	}


} // end of ldapauthmanager_log_main



// call class
$obj_main		= New ldapauthmanager_log_main;

$obj_main->authenticate();
$obj_main->log_watch();

log_write("notification", "script", "Terminating logging process for ldapauthmanager");



?>
