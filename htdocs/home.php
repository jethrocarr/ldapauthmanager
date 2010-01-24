<?php
/*
	Summary/Welcome page for ldapauthmanager
*/

if (!user_online())
{
	// Because this is the default page to be directed to, if the user is not
	// logged in, they should go straight to the login page.
	//
	// All other pages will display an error and prompt the user to login.
	//
	include_once("user/login.php");
}
else
{
	class page_output
	{
		function check_permissions()
		{
			// this page has a special method for handling permissions - please refer to code comments above
			return 1;
		}

		function check_requirements()
		{
			// nothing todo
			return 1;
		}
			
		function execute()
		{
			// nothing todo
			return 1;
		}

		function render_html()
		{
			print "<h3>OVERVIEW</h3>";
			print "<p>Welcome to <a target=\"new\" href=\"http://www.amberdms.com/ldapauthmanager\">LDAPAuthManager</a>, an open-source, PHP web-based LDAP authentication management interface designed to make it easy to manager users running on centralised authentication environments.</p>";

		}
	}
}

?>
