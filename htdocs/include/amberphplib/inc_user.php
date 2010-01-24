<?php
/*
	inc_user.php

	Provides user management and authentication functions.
*/



/*
	CLASS USER_AUTH

	The user_auth class provides authentication functions for Amberphplib based applications using
	a varity of backends.

	Currently supported are:
	* sql		use tables in a SQL database.
	* ldap		authenticate using LDAP but use SQL database for user information
				(allows finer control over what users can login)
	* ldaponly	authenticate using LDAP and also use LDAP for all user information
				(all user information is from LDAP, allowing any user to login, but access is stricted by using groups in permissions_get())

	Refer to Amberphplib developer documentation for futher information on authentication.
*/


class user_auth
{
	var $method;		// mode of authentication

	var $blacklist_enable;	// blacklist on/off
	var $blacklist_limit;	// max num attempts before being blacklisted


	/*
		Constructor
	*/

	function user_auth()
	{
		log_debug("user_auth", "Executing user_auth()");

		// fetch authentication method from the database. If that fails, default to sql
		$this->method = sql_get_singlevalue("SELECT value FROM `config` WHERE name='AUTH_METHOD' LIMIT 1");

		if (!$this->method)
		{
			$this->method = "sql";
		}
	}



	/*
		check_online()

		This function works by checking the user's authentication key from their session data against the SQL
		database to verify that their IP has not changed, and that they are who they say they are.

		Returns
		0		Not Logged In
		1		User is logged in
	*/

	function check_online()
	{
		log_debug("user_auth", "Executing user_online()");

		if (empty($_SESSION["user"]["authkey"]))				// if user has no login data, don't bother trying to check
			return 0;
		if (!preg_match("/^[a-zA-Z0-9]*$/", $_SESSION["user"]["authkey"]))	// make sure the key is valid info, NOT AN SQL INJECTION.
			return 0;

		if (isset($GLOBALS["cache"]["user"]["online"]))
		{
			// we have already checked if the user is online, so don't bother checking again
			return 1;
		}
		else
		{
			// get user session data
			$sql_session_obj		= New sql_query;
			$sql_session_obj->string 	= "SELECT id, time FROM `users_sessions` WHERE authkey='" . $_SESSION["user"]["authkey"] . "' AND ipaddress='" . $_SERVER["REMOTE_ADDR"] . "' LIMIT 1";
			$sql_session_obj->execute();

			if ($sql_session_obj->num_rows())
			{
				$sql_session_obj->fetch_array();

				$time = time();
				if ($time < ($sql_session_obj->data[0]["time"] + 7200))
				{
					// we want to update the time value in the database, but we don't want to do this
					// on every single page load - no need, and a waste of performance.
					//
					// therefore, we only update the time record in the DB if it's older than 30 minutes. We use
					// this time to see if the user has been inactive for long periods of time, to log them out.
					if (($time -  $sql_session_obj->data[0]["time"]) > 1800)
					{
						// update time field
						$sql_obj		= New sql_query;
						$sql_obj->string	= "UPDATE `users_sessions` SET time='$time' WHERE authkey='". $_SESSION["user"]["authkey"] ."' LIMIT 1";
						$sql_obj->execute();
					}

					// save to cache
					$GLOBALS["cache"]["user"]["online"] = 1;

					// user is logged in.
					return 1;
				}
				else
				{
					// The user hasn't accessed a page for 2 hours, we log em' out for security reasons.
					
					// We save the query string, so they can easily log back in to where they were.			
					$_SESSION["login"]["previouspage"] = $_SERVER["QUERY_STRING"];
				
					// log user out
					user_logout();

					// set the timeout flag. (so the login message is different)
					$_SESSION["user"]["timeout"] = "flagged";
				}
			}
		}

		
		return 0;

	} // end of check_online()




	/*
		login($instance, $username, $password)

		This function performs two main tasks:
		* If enabled, it performs brute-force blacklisting defense, and will block authentication
		  attempts from blacklisted IP addresses.
		* Checks the username/password and authenticates the user.

		Return Codes
		-5	Login disabled due to database-application version mismatch
		-4	Instance has been disabled
		-3	Invalid instance ID
		-2	User account has been disabled
		-1	IP is blacklisted due to brute-force attempts
		0	Invalid username/password
		1	Success
	*/
	function login($instance, $username, $password)
	{
		log_debug("user_auth", "Executing login($instance, $username, \$password)");

		
		/*
			Make sure it's safe to allow users to login
		*/

		$schema_version = sql_get_singlevalue("SELECT value FROM config WHERE name='SCHEMA_VERSION' LIMIT 1");

		if ($schema_version != $GLOBALS["config"]["schema_version"])
		{
			log_write("error", "user_auth", "The application has been updated, but the database has not been upgraded to match. Login is disabled until this is resolved.");
			return -5;
		}



		/*
			Run Authentication Process
		*/

		// connect to correct database instance
		$return = $this->login_instance_init($instance);

		if ($return == "-2")
		{
			// instance has been disabled
			return -4;
		}
		elseif ($return != "1")
		{
			// invalid instance ID or unknown error
			return -3;
		}



		// verify IP against blacklist
		$obj_blacklist	= New blacklist;
		
		if ($obj_blacklist->check_ip())
		{
			// blacklisted user
			return -1;
		}


		// authenticate the user
		$userid = $this->login_authenticate($username, $password);

		if ($userid != 1)
		{
			if ($userid == "-2")
			{
				// user is disabled and can not be authenticated
				return -2;
			}
			else
			{
				// invalid password or unknown failure occured
				$obj_blacklist->increment();
			
				return 0;
			}
		}


		// Create user authentication session
		if (!$this->session_init($userid, $username))
		{
			// unknown failure occured
			$obj_blacklist->increment();

			return 0;
		}



		/*
			If enabled, run the phone home feature now - this submits non-private
			data to Amberdms to better understand the size and requirements of
			our userbase.
		*/

		$phone_home = New phone_home();

		if ($phone_home->check_enabled())
		{
			if ($phone_home->check_phone_home_timer())
			{
				// time to update
				$phone_home->stats_generate();
				$phone_home->stats_submit();
			}
		}



		/*
			Login Successful!
		*/
		return 1;

	} // end of login()





	/*
		login_instance_init

		Fields
		instance	Name of database instance

		Returns
		-2	Instance has been disabled
		-1	Invalid instance ID
		 0	Unknown Error
		 1	Successfully changed to database instance
	*/
	function login_instance_init($instance)
	{
		log_debug("user_auth", "Executing login_instance_init($instance)");


		// check the instance (if required) and select the required database
		if ($GLOBALS["config"]["instance"] == "hosted")
		{
			$sql_instance_obj		= New sql_query;
			$sql_instance_obj->string	= "SELECT active, db_hostname FROM `instances` WHERE instanceid='$instance' LIMIT 1";
			$sql_instance_obj->execute();
			
			if ($sql_instance_obj->num_rows())
			{
				$sql_instance_obj->fetch_array();

				if ($sql_instance_obj->data[0]["active"])
				{
					// Instance exists and access is permitted - now use the details
					// to establish a connection to the instance database (note that this
					// database may be on a different server)


					// if the hostname is blank, default to the current
					if ($sql_instance_obj->data[0]["db_hostname"] == "")
					{
						$sql_instance_obj->data[0]["db_hostname"] = $GLOBALS["config"]["db_host"];
					}

					// if the instance database is on a different server, initate a connection
					// to the new server.
					if ($sql_instance_obj->data[0]["db_hostname"] != $GLOBALS["config"]["db_host"])
					{
						// TODO: does this connect statement need to be moved into the sql_obj framework?
						$link = mysql_connect($sql_instance_obj->data[0]["db_hostname"], $config["db_user"], $config["db_pass"]);

						if (!$link)
						{
							log_write("error", "user_auth", "Unable to connect to database server for instance $instance - error: " . mysql_error());
							return -1;
						}
					}


					// select the instance database
					$dbaccess = mysql_select_db($GLOBALS["config"]["db_name"] ."_$instance");
		
					if (!$dbaccess)
					{
						// invalid instance ID
						// ID has a record in the instance table, but does not have a valid database
						log_write("error", "user_auth", "Instance ID has record but no database accessible - error: ". mysql_error());
						return -1;
					}
					else
					{
						// save the instance value
						$_SESSION["user"]["instance"]["id"]		= $instance;
						$_SESSION["user"]["instance"]["db_hostname"]	= $sql_instance_obj->data[0]["db_hostname"];
					}
				}
				else
				{
					// instance exists but is disabled
					log_write("error", "user_auth", "Your account has been disabled - please contact the system administrator if you belive this to be a mistake.");
					return -2;
				}
			}
			else
			{
				// no such instance
				log_write("error", "user_auth", "Please provide a valid customer instance ID.");
				return -1;
			}

		}

		// success, running correct database instance
		return 1;


	} // end of login_instance_init




	/*
		login_authenticate

		Verifies if the supplied username and password is valid.

		Fields
		username		Username
		password		Password (plain-text)

		Returns
		-2			User account is disabled.
		-1			Unknown Failure
		0			Failure to authenticate
		#			Successful authentication, ID of user returned
	*/

	function login_authenticate($username, $password)
	{
		log_debug("user_auth", "Executing login_authenticate($username, \$password)");

		switch ($this->method)
		{
			case "sql":
			default:
				/*
					SQL Authentication

					Authenticate against `users` SQL table and return row ID
				*/


				// get user data
				$sql_user_obj		= New sql_query;
				$sql_user_obj->string	= "SELECT id, password, password_salt FROM `users` WHERE username='$username' LIMIT 1";
				$sql_user_obj->execute();

				if ($sql_user_obj->num_rows())
				{
					$sql_user_obj->fetch_array();

					// compare passwords
					if ($sql_user_obj->data[0]["password"] == sha1($sql_user_obj->data[0]["password_salt"] . "$password"))
					{
						/*
							Password is valid, ensure account is not disabled
						*/

						// make sure the user is not disabled. (PERM ID = 1)
						$sql_perms_obj		= New sql_query;
						$sql_perms_obj->string	= "SELECT id FROM `users_permissions` WHERE userid='" . $sql_user_obj->data[0]["id"] . "' AND permid='1' LIMIT 1";
						$sql_perms_obj->execute();

						if ($sql_perms_obj->num_rows())
						{
							// user has been disabled
							log_write("error", "user_auth", "Your user account has been disabled. Please contact the system administrator to get it unlocked.");
							return -2;
						}
						else
						{
							// authentication was successful :-)

							// update user's last-login data
							$sql_obj		= New sql_query;
							$sql_obj->string	= "UPDATE `users` SET ipaddress='". $_SERVER["REMOTE_ADDR"]  ."', time='". time() ."' WHERE id='" . $sql_user_obj->data[0]["id"] . "'";
							$sql_obj->execute();

							// does the user need to change their password? If they have no salt, it means the password
							// is the system default and needs to be changed
							if ($sql_user_obj->data[0]["password_salt"] == "")
							{
								$_SESSION["error"]["message"][] = "Your password is currently set to a default. It is highly important for you to change this password, which you can do <a href=\"index.php?page=user/options.php\">by clicking here</a>.";
							}


							// return user ID
							return $sql_user_obj->data[0]["id"];

						} // end if authentication successful

					} // end if password valid
					else
					{
						log_debug("user_auth", "Authentication failed due to incorrect password/username combination");
					}

				} // end if user exists

			break;

		} // end of switch authentication method


		// unknown failure
		log_debug("user_auth", "An unknown error occured whilst attempting to authenticate");
		return -1;

	} // end of login_authenticate



	/*
		logout

		Logs the user out of the system and clears all session variables relating to their connection.

		Returns
		0	Unexpected Error
		1	Success
	*/
	function logout()
	{
		log_debug("user_auth", "Executing logout()");

		// terminate user session
		return $this->session_terminate();

	} // end of logout





	/*
		session_init

		Calling this function will create an active session for the user - make sure you
		authenticate with login_authenticate first before calling this function, since
		this function does not do that.

		Fields
		userid		ID of User
		username	Username

		Returns
		0		Unable to initiate user session (unexpected failure)
		1		Success
	*/
	function session_init($userid, $username)
	{
		log_debug("user_auth", "Executing session_init($userid, $username)");


		/*
			We have verified that the user is valid. We now assign them an authentication key, which is
			like an additional session ID.
			
			This key is tied to their IP address, so if their IP changes, the user must re-authenticate.
			
			Most of the purpose of this auth key, is already provided by PHP sessions, but this key
			method, provides additional protection in the event of any of the following scenarios:
			
			* PHP being used with session IDs passed via GET (since the attackers IP will most
			   likely be different)
			
			* An exploit in the PHP session handling that allows a user to change their session
			  information.
			
			* An exploit elsewhere in this application which allows the changing of any session variable will
			  not allow a user to gain different authentication rights.
			
			The authentication key is stored in the seporate users_sessions tables, which is capable
			of supporting concurrent logins. The session table will automatically clean out any expired
			session records whenever a user logs in.
			
			Note: The users_sessions table is intentionally not a memory table, in order to support this application
			when running on load-balancing clusters with replicated MySQL databases. If this application is
			running on a standalone server only, a memory table would have been acceptable.
		*/

		// get other information - IP address & time
		$ipaddress	= $_SERVER["REMOTE_ADDR"];
		$time		= time();

			
		// generate an authentication key
		$feed = "0123456789abcdefghijklmnopqrstuvwxyz";
		$authkey = null;
		for ($i=0; $i < 40; $i++)
		{
			$authkey .= substr($feed, rand(0, strlen($feed)-1), 1);
		}


		// perform session table cleanup - remove any records older than 12 hours
		$time_expired = $time - 43200;

		$sql_obj		= New sql_query;
		$sql_obj->string	= "DELETE FROM `users_sessions` WHERE time < '$time_expired'";
		$sql_obj->execute();


		// if concurrent logins is not enabled, delete any old sessions belonging to this user.
		if (sql_get_singlevalue("SELECT value FROM users_options WHERE userid='". $userid ."' AND name='concurrent_logins' LIMIT 1") != "on")
		{
			log_write("debug", "inc_users", "User account does not permit concurrent logins, removing all old sessions");

			$sql_obj		= New sql_query;
			$sql_obj->string	= "DELETE FROM `users_sessions` WHERE userid='". $userid ."'";
			$sql_obj->execute();
		}


		// create session entry for user login
		$sql_obj		= New sql_query;
		$sql_obj->string	= "INSERT INTO `users_sessions` (userid, authkey, ipaddress, time) VALUES ('$userid', '$authkey', '$ipaddress', '$time')";
		$sql_obj->execute();


		// set session variables
		$_SESSION["user"]["id"]		= $userid;
		$_SESSION["user"]["name"]	= $username;
		$_SESSION["user"]["authkey"]	= $authkey;


		// fetch user options from the database (if any)
		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT name, value FROM users_options WHERE userid='". $userid ."'";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			$sql_obj->fetch_array();
				
			foreach ($sql_obj->data as $data)
			{
				$_SESSION["user"][ $data["name"] ] = $data["value"];
			}
		}



		// success
		return 1;

	} // end of session_init


	/*
		session_terminate

		Logs the user out of the system and clears all session variables relating to their connection.

		Returns
		0	Unexpected Error
		1	Success
	*/
	function session_terminate()
	{
		log_debug("user_auth", "Executing session_terminate()");

		if ($_SESSION["user"]["authkey"])
		{
			// remove session entry from DB
			$sql_obj		= New sql_query;
			$sql_obj->string	= "DELETE FROM `users_sessions` WHERE authkey='" . $_SESSION["user"]["authkey"] . "' LIMIT 1";
			$sql_obj->execute();
		}

		// log the user out.
		$GLOBALS["cache"]["user"]	= array();
		$_SESSION["user"]		= array();
		$_SESSION["form"]		= array();

		return 1;

	} // end of session_terminate





} // end of class user_auth




/*
	CLASS BLACKLIST

	In the ideal world there would be no need to worry about brute-force attacks, however the reality is that often
	web-based applications are exposed to the public internet and need some protection against outside attack.

	The blacklist class and functions provides the ability to check and block IP addresses that are attacking
	and trying to guess usernames/passwords.

	TODO: in future this class will be made more generic, to blacklist for things other than users, such as
	excessive resource usage or DOS-style attacks against site APIs.
*/
class blacklist
{
	var $blacklist_enable;		// whether or not features are enabled/disabled
	var $blacklist_limit;		// max num of attempts before blacklisting
	var $ipaddress;			// address to run functions against.


	/*
		Constructor
	*/
	function blacklist()
	{
		// default IP address to use
		$this->ipaddress		= $_SERVER["REMOTE_ADDR"];

		// fetch blacklist configuration
		$this->blacklist_enable		= sql_get_singlevalue("SELECT value FROM `config` WHERE name='BLACKLIST_ENABLE' LIMIT 1");
		$this->blacklist_limit		= sql_get_singlevalue("SELECT value as blacklist_limit FROM `config` WHERE name='BLACKLIST_LIMIT' LIMIT 1");

	}



	/*
		check_ip

		Checks the provided or accessing IP address against the blacklist database
		and checks if it exists in there.

		Returns
		0		No match
		1		Match
	*/
	function check_ip()
	{
		log_debug("user_auth", "Executing blacklist_check_ip()");


		if ($this->blacklist_enable == "enabled")
		{
			// check the database - is this IP in the bad list?
			$sql_blacklist_obj		= New sql_query;
			$sql_blacklist_obj->string	= "SELECT failedcount, time FROM `users_blacklist` WHERE ipaddress='" . $this->ipaddress . "'";
			$sql_blacklist_obj->execute();

			if ($sql_blacklist_obj->num_rows())
			{
				foreach ($sql_blacklist_obj->data as $data_blacklist)
				{
					// IP is in bad list - but we need to check the count against the time, to see if it's just an
					// innocent wrong password, or if it's something more sinister.

					if ($data_blacklist["failedcount"] >= $this->blacklist_limit && $data_blacklist["time"] >= (time() - 432000))
					{
						// if failed count >= blacklist limit, and if the last attempt was within
						// the last 5 days, block the user.

						log_write("error", "blacklist", "For brute-force security reasons, you have been locked out of the system interface.");
						return 1;
					}
					elseif ($data_blacklist["time"] < (time() - 432000))
					{
						// It has been more than 5 days since the last attempt was blocked. Start clearing the counter, by
						// removing 2 attempts.
						
						if ($data_blacklist["failedcount"] > 2)
						{
							// decrease by 2.
							$newcount		= $data_blacklist["failedcount"] - 2;

							$sql_obj		= New sql_query;
							$sql_obj->string	= "UPDATE `users_blacklist` SET `failedcount`='$newcount' WHERE ipaddress='" . $this->ipaddress . "' LIMIT 1";
							$sql_obj->execute();

							// IP is still blacklisted
							return 1;
						}
						else
						{
							// time to remove the entry completely
							$sql_obj		= New sql_query;
							$sql_obj->string	= "DELETE FROM `users_blacklist` WHERE ipaddress='" . $this->ipaddress . "' LIMIT 1";
							$sql_obj->execute();

							// IP is no-longer blacklisted
							return 0;
						}
					}
				}
			}
			else
			{
				// IP is not blacklisted
				return 0;
			}

		} // end of if blacklist enabled

	} // end of check_ip()



	/*
		increment

		This function increments/add an entry to the blacklist database and should
		be called everytime authentication has failed.

		Returns
		0	Unexpected Failure
		1	Success
	*/

	function increment()
	{
		log_debug("user_auth", "Executing increment()");


		// add time delay to reduce effectiveness of rapid attacks.
		sleep(2);


		if ($this->blacklist_enable == "enabled")
		{
			// check if there is already an entry.
			$sql_blacklist_obj		= New sql_query;
			$sql_blacklist_obj->string	= "SELECT failedcount FROM `users_blacklist` WHERE ipaddress='" . $this->ipaddress . "'";
			$sql_blacklist_obj->execute();

			if ($sql_blacklist_obj->num_rows())
			{
				$sql_blacklist_obj->fetch_array();

				// IP is in the list. Increase the failed count, and set the time to now.
				foreach ($sql_blacklist_obj->data as $data_blacklist)
				{
					$newcount       	= $data_blacklist["failedcount"] + 1;
					$newtime        	= time();

					$sql_obj		= New sql_query;
					$sql_obj->string	= "UPDATE `users_blacklist` SET `failedcount`='$newcount', time='$newtime' WHERE ipaddress='" . $this->ipaddress . "'";
					$sql_obj->execute();
				}
			}
			else
			{
				// IP is not currently in the list. We need to add it.
				$newtime       		= time();

				$sql_obj		= New sql_query;
				$sql_obj->string	= "INSERT INTO `users_blacklist` (ipaddress, failedcount, time) VALUES ('". $this->ipaddress ."', '1', '$newtime')";
				$sql_obj->execute();
			}
			
		}

		return 1;
	
	} // end of blacklist_increment
	

} // end of class blacklist







/*
	***************************************************
			STANDALONE FUNCTIONS
	***************************************************
*/



/*
	user_online

	Wrapper function for user_auth->check_online()
*/
function user_online()
{
	log_debug("inc_user", "Executing user_online()");

	$obj_user = New user_auth;

	return $obj_user->check_online();
}


/*
	user_login

	wrapper function for user_auth->login()
*/
function user_login($instance, $username, $password)
{
	log_write("debug", "inc_user", "executing user_login()");
	log_write("warning", "inc_user", "deprecated use of user_login");


	$obj_user = new user_auth;

	return $obj_user->login($instance, $username, $password);
}


/*
	user_logout

	wrapper function for user_auth->logout()
*/
function user_logout()
{
	log_write("debug", "inc_user", "executing user_logout()");
	log_write("warning", "inc_user", "deprecated use of user_logout");


	$obj_user = new user_auth;

	return $obj_user->logout();
}



/*
	user_newuser($username, $password, $realname, $email)

	Creates a new user account in the database and returns the ID of the new user account.
*/
function user_newuser($username, $password, $realname, $email)
{
	log_debug("inc_user", "Executing user_newuser($username, $password, $realname, $email)");

	// make sure that the user running this command is an admin
	if (user_permissions_get("admin"))
	{
		// verify data
		if ($username && $password && $realname && $email)
		{
			// TODO: Fix ACID compliance here

			// create the user account
			$sql_obj		= New sql_query;
			$sql_obj->string	= "INSERT INTO `users` (username, realname, contact_email) VALUES ('$username', '$realname', '$email')";
			$sql_obj->execute();

			$userid = $sql_obj->fetch_insert_id() ;

			// set the password
			user_changepwd($userid, $password);

			return $userid;

		} // if data is valid
		
	} // if user is an admin

	return 0;
}



/*
	user_changepwd($userid, $password)

	Updates the user's password - regenerates the password salt and hashes
	the password with the salt and SHA algorithm.

	Returns 1 on success, 0 on failure.
*/
function user_changepwd($userid, $password)
{
	log_debug("inc_user", "Executing user_changepwd($userid, password)");

	if (user_permissions_get("admin"))
	{
		if ($userid && $password)
		{
			//
			// Here we generate a password salt. This is used, so that in the event of an attacker
			// getting a copy of the users table, they can't brute force the passwords using pre-created
			// hash dictionaries.
			//
			// The salt requires them to have to re-calculate each password possibility for any passowrd
			// they wish to try and break.
			//
			$feed		= "0123456789abcdefghijklmnopqrstuvwxyz";
			$password_salt	= null;

			for ($i=0; $i < 20; $i++)
			{
				$password_salt .= substr($feed, rand(0, strlen($feed)-1), 1);
			}				
			
			// encrypt password with salt
			$password_crypt = sha1("$password_salt"."$password");

			// apply changes to DB.
			$sql_obj		= New sql_query;
			$sql_obj->string	= "UPDATE `users` SET password='$password_crypt', password_salt='$password_salt' WHERE id='$userid' LIMIT 1";
			$sql_obj->execute();
		
			return 1;

		} // if data is valid
		
	} // if user is an admin

	return 0;
}



/*
	user_permissions_get($type)

	This function looks up the database to see if the user has the specified permission. If so,
	the function will return 1.

	If the user does not have the permission, the function will return 0.
*/
function user_permissions_get($type)
{
	log_debug("inc_user", "Executing user_permissions_get($type)");


	// everyone (including guests) have the "public" permission, so don't waste cycles checking for it
	if ($type == "public")
	{
		return 1;
	}


	if (isset($GLOBALS["cache"]["user"]["perms"][$type]))
	{
		return 1;
	}
	else
	{
		// other permissions... make sure user is valid, and logged in.
		if ($userid = user_information("id"))
		{
			// get the id of the permission
			$permid = sql_get_singlevalue("SELECT id as value FROM `permissions` WHERE value='$type' LIMIT 1");

			// if nothing found, deny.
			if (!$permid)
				return 0;

			// see if the user has this particular permission.
			$sql_obj		= New sql_query;
			$sql_obj->string	= "SELECT id FROM `users_permissions` WHERE userid='$userid' AND permid='$permid' LIMIT 1";
			$sql_obj->execute();

			if ($sql_obj->num_rows())
			{
				// user has an entry for that permission.

				// save to cache & return success
				$GLOBALS["cache"]["user"]["perms"][$type] = 1;
				return 1;
				
			} // if permission exists
			
		} // if user is logged in
	}
	
	// default to deny
	return 0;
}



/*
	user_information($field)

	This function looks up the specified field in the database's "users" table and returns the result.
*/
function user_information($field)
{
	log_debug("inc_user", "Executing user_information($field)");


	if (isset($GLOBALS["cache"]["user"]["info"][$field]))
	{
		return $GLOBALS["cache"]["user"]["info"][$field];
	}
	else
	{
		// verify user is logged in
		if (user_online())
		{
			// fetch the value
			$value = sql_get_singlevalue("SELECT $field as value FROM `users` WHERE username='" . $_SESSION["user"]["name"] . "' LIMIT 1");

			// cache the value
			$GLOBALS["cache"]["user"]["info"][$field] = $value;

			// return the value
			return $value;
		}
	}

	return 0;
}





/*
	user_permissions_staff_get($type, $staffid)

	This function looks up the database to see if the user has the specified permission
	in their access rights configuration for the requested employee.

	If the user has the correct permissions for this employee, the function will return 1,
	otherwise the function will return 0.
*/
function user_permissions_staff_get($type, $staffid)
{
	log_debug("user/permissions_staff", "Executing user_permissions_staff_get($type, $staffid)");

	// get ID of permissions record
	$sql_query		= New sql_query;
	$sql_query->string	= "SELECT id FROM permissions_staff WHERE value='$type' LIMIT 1";
	$sql_query->execute();
	
	if ($sql_query->num_rows())
	{
		$sql_query->fetch_array();
		$permid	= $sql_query->data[0]["id"];

		// check if the user has this permission for this staff member
		$user_perms		= New sql_query;
		$user_perms->string	= "SELECT id FROM users_permissions_staff WHERE userid='". $_SESSION["user"]["id"] ."' AND staffid='$staffid' AND permid='$permid' LIMIT 1";
		$user_perms->execute();

		if ($user_perms->num_rows())
		{
			// user has permissions
			return 1;
		}
	}
	
	return 0;
}


/*
	user_permissions_staff_getarray($type)

	This functions returns an array of all the staff IDs that the current user
	has access too.
*/
function user_permissions_staff_getarray($type)
{
	log_debug("user/permissions_staff", "Executing user_permissions_staff_getarray($type)");


	// get ID of permissions record
	$sql_query		= New sql_query;
	$sql_query->string	= "SELECT id FROM permissions_staff WHERE value='$type' LIMIT 1";
	$sql_query->execute();
	
	if ($sql_query->num_rows())
	{
		$sql_query->fetch_array();
		$permid	= $sql_query->data[0]["id"];

		$access_staff_ids = array();

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT staffid FROM `users_permissions_staff` WHERE userid='". $_SESSION["user"]["id"] ."' AND permid='$permid'";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			$sql_obj->fetch_array();

			foreach ($sql_obj->data as $data_sql)
			{
				$access_staff_ids[] = $data_sql["staffid"];
			}
		}

		unset($sql_obj);

	}

	return $access_staff_ids;	
}




?>
