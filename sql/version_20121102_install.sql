--
-- LDAPAUTHMANAGER APPLICATION
--
-- Inital database install SQL.
--

CREATE DATABASE `ldapauthmanager` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ldapauthmanager`;



--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`name`, `value`) VALUES
('APP_MYSQL_DUMP', '/usr/bin/mysqldump'),
('APP_PDFLATEX', '/usr/bin/pdflatex'),
('AUTH_METHOD', 'ldaponly'),
('AUTO_INT_GID', '2005'),
('AUTO_INT_UID', '1020'),
('BLACKLIST_ENABLE', ''),
('BLACKLIST_LIMIT', '10'),
('DATA_STORAGE_LOCATION', 'use_database'),
('DATA_STORAGE_METHOD', 'database'),
('DATEFORMAT', 'yyyy-mm-dd'),
('LANGUAGE_DEFAULT', 'en_us'),
('LANGUAGE_LOAD', 'preload'),
('PATH_TMPDIR', '/tmp'),
('PHONE_HOME', 'disabled'),
('PHONE_HOME_TIMER', ''),
('SCHEMA_VERSION', '1'),
('SUBSCRIPTION_ID', '5f4d732e933c8ac621d99c0e2a15a536'),
('SUBSCRIPTION_SUPPORT', 'opensource'),
('TIMEZONE_DEFAULT', 'SYSTEM'),
('UPLOAD_MAXBYTES', '5242880');

-- --------------------------------------------------------

--
-- Table structure for table `file_uploads`
--

CREATE TABLE IF NOT EXISTS `file_uploads` (
  `id` int(11) NOT NULL auto_increment,
  `customid` int(11) NOT NULL default '0',
  `type` varchar(20) NOT NULL,
  `timestamp` bigint(20) unsigned NOT NULL default '0',
  `file_name` varchar(255) NOT NULL,
  `file_size` varchar(255) NOT NULL,
  `file_location` char(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



--
-- Table structure for table `file_upload_data`
--

CREATE TABLE IF NOT EXISTS `file_upload_data` (
  `id` int(11) NOT NULL auto_increment,
  `fileid` int(11) NOT NULL default '0',
  `data` blob NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table for use as database-backed file storage system' AUTO_INCREMENT=1 ;



--
-- Table structure for table `journal`
--

CREATE TABLE IF NOT EXISTS `journal` (
  `id` int(11) NOT NULL auto_increment,
  `locked` tinyint(1) NOT NULL default '0',
  `journalname` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `userid` int(11) NOT NULL default '0',
  `customid` int(11) NOT NULL default '0',
  `timestamp` bigint(20) unsigned NOT NULL default '0',
  `content` text NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `journalname` (`journalname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL auto_increment,
  `language` varchar(20) NOT NULL,
  `label` varchar(255) NOT NULL,
  `translation` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `language` (`language`),
  KEY `label` (`label`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=301 ;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES
(292, 'en_us', 'username_ldapauthmanager', 'Username'),
(293, 'en_us', 'password_ldapauthmanager', 'Password'),
(294, 'en_us', 'user_account', 'My Account'),
(295, 'en_us', 'manage_users', 'Manage Users'),
(296, 'en_us', 'manage_groups', 'Manage Groups'),
(297, 'en_us', 'tbl_lnk_details', 'details'),
(298, 'en_us', 'tbl_lnk_permissions', 'groups'),
(299, 'en_us', 'tbl_lnk_delete', 'delete'),
(300, 'en_us', 'configuration', 'Configuration'),
(301, 'en_us', 'user_view', 'User Details'),
(302, 'en_us', 'user_password', 'User Password'),
(303, 'en_us', 'username', 'Username'),
(304, 'en_us', 'realname', 'Real Name'),
(305, 'en_us', 'uidnumber', 'UID Number'),
(306, 'en_us', 'gidnumber', 'GID Number'),
(307, 'en_us', 'loginshell', 'Login Shell'),
(308, 'en_us', 'password', 'Password'),
(309, 'en_us', 'password_confirm', 'Password (confirm)'),
(310, 'en_us', 'submit', 'Apply'),
(311, 'en_us', 'groupname', 'Group Name'),
(312, 'en_us', 'memberuid', 'Members of Group'),
(313, 'en_us', 'group_view', 'Group Details'),
(314, 'en_us', 'group_members', 'Members of Group'),
(315, 'en_us', 'group_delete', 'Delete Group'),
(316, 'en_us', 'user_delete', 'Delete User'),
(317, 'en_us', 'delete_confirm', 'Confirm Deletion'),
(318, 'en_us', 'delete', 'Delete'),
(319, 'en_us', 'config_seed', 'UID/GID Seed Configuration'),
(320, 'en_us', 'config_security', 'Security Configuration'),
(321, 'en_us', 'config_dateandtime', 'Date and Time Configuration'),
(322, 'en_us', 'user_groups', 'User Group Membership'),
(323, 'en_us', 'homedirectory', 'Home Directory');

-- --------------------------------------------------------

--
-- Table structure for table `language_avaliable`
--

CREATE TABLE IF NOT EXISTS `language_avaliable` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `language_avaliable`
--

INSERT INTO `language_avaliable` (`id`, `name`) VALUES
(1, 'en_us');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL auto_increment,
  `priority` int(11) NOT NULL default '0',
  `parent` varchar(50) NOT NULL,
  `topic` varchar(50) NOT NULL,
  `link` varchar(50) NOT NULL,
  `permid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=182 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES
(170, 150, 'top', 'user_account', 'user/account.php', 0),
(171, 900, 'top', 'configuration', 'admin/config.php', 2),
(172, 200, 'top', 'manage_users', 'user_management/users.php', 2),
(173, 300, 'top', 'manage_groups', 'group_management/groups.php', 2),
(174, 201, 'manage_users', '', 'user_management/user-view.php', 2),
(175, 201, 'manage_users', '', 'user_management/user-delete.php', 2),
(176, 201, 'manage_users', '', 'user_management/user-permissions.php', 2),
(177, 201, 'manage_users', '', 'user_management/user-add.php', 2),
(178, 301, 'manage_groups', '', 'group_management/group-view.php', 2),
(179, 301, 'manage_groups', '', 'group_management/group-add.php', 2),
(180, 301, 'manage_groups', '', 'group_management/group-delete.php', 2),
(181, 100, 'top', 'Overview', 'home.php', 0);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL auto_increment,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores all the possible permissions' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `value`, `description`) VALUES
(1, 'disabled', 'Enabling the disabled permission will prevent the user from being able to login.'),
(2, 'ldapadmins', 'Provides access to user and configuration management features (note: any user with admin can provide themselves with access to any other section of this program)');



--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `realname` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_salt` varchar(20) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `time` bigint(20) NOT NULL default '0',
  `ipaddress` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ipaddress` (`ipaddress`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='User authentication system.' AUTO_INCREMENT=1 ;



--
-- Table structure for table `users_blacklist`
--

CREATE TABLE IF NOT EXISTS `users_blacklist` (
  `id` int(11) NOT NULL auto_increment,
  `ipaddress` varchar(15) NOT NULL,
  `failedcount` int(11) NOT NULL default '0',
  `time` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Prevents automated login attacks.' AUTO_INCREMENT=1 ;



--
-- Table structure for table `users_options`
--

CREATE TABLE IF NOT EXISTS `users_options` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- Table structure for table `users_permissions`
--

CREATE TABLE IF NOT EXISTS `users_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `permid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores user permissions.' AUTO_INCREMENT=2 ;


--
-- Table structure for table `users_sessions`
--

CREATE TABLE IF NOT EXISTS `users_sessions` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL,
  `authkey` varchar(40) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `time` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



--
-- Additional Upgrades
--

INSERT INTO `config` (`name`, `value`) VALUES ('FEATURE_RADIUS', 'disabled');
INSERT INTO `config` (`name`, `value`) VALUES ('FEATURE_RADIUS_MAXVENDOR', '10');

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'config_features', 'Feature Configuration');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'tbl_lnk_radius', 'radius attr');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'radius_attr', 'Standard Radius Attributes');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'radius_attr_vendor', 'Vendor-Specific Radius Attributes');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'filter_searchbox', 'Search Box');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'filter_hide_user_group_maps', 'Hide User-Groups');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL , 'en_us', 'gn', 'Given Name');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL , 'en_us', 'sn', 'Surname');

INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES (NULL, '201', 'manage_users', '', 'user_management/user-radius.php', '2');



--
-- Upgrades for new server management and logging features
--


CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL auto_increment,
  `id_server` int(11) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `log_type` char(10) NOT NULL,
  `log_contents` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `ldap_servers` (
  `id` int(11) NOT NULL auto_increment,
  `server_name` varchar(255) NOT NULL,
  `server_description` text NOT NULL,
  `api_auth_key` varchar(255) NOT NULL,
  `api_sync_log` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


INSERT INTO `config` (`name`, `value`) VALUES ('LOG_UPDATE_INTERVAL', '10');
INSERT INTO `config` (`name`, `value`) VALUES ('AUTH_PERMS_CACHE', 'enabled');

INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES (NULL, 175, 	'top', 'menu_logs', 'logs/logs.php', 2);
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES (NULL, 500, 'top', 'menu_ldap_servers', 'servers/servers.php', 2);
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES(NULL, 501, 'menu_ldap_servers', 'menu_ldap_servers_view', 'servers/servers.php', 2);
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES(NULL, 502, 'menu_ldap_servers', 'menu_ldap_servers_add', 'servers/add.php', 2);
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES(NULL, 510, 'menu_ldap_servers_view', '', 'servers/view.php', 2);
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES(NULL, 510, 'menu_ldap_servers_view', '', 'servers/logs.php', 2);
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES(NULL, 510, 'menu_ldap_servers_view', '', 'servers/delete.php', 2);


INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'config_logging', 'Logging Configuration');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'menu_logs', 'LDAP Logs');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'server_name', 'Server Name');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'timestamp', 'Timestamp');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'log_type', 'Type');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'log_contents', 'Message');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'filter_num_logs_rows', 'Max Number of Log');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'menu_ldap_servers', 'Manage LDAP Servers');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'menu_ldap_servers_view', 'View Servers');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'menu_ldap_servers_add', 'Add New Server');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'tbl_lnk_logs', 'logs');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'server_description', 'Server Description');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'sync_log_status', 'Logging Sync Status');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'status_log_synced', 'Synced');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'status_log_unsynced', 'Unsynced/Problem');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'server_details', 'Server Details');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'server_api', 'Server API Settings');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'api_auth_key', 'SOAP API Auth Key');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'help_api_auth_key', 'Authentication key used to grant access to upload log information');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'server_status', 'Server Status/Information');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'sync_status_log', 'Logging Sync Status');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'server_delete', 'Delete Server');



--
-- Changes for Mikrotik vendor specific attributes
--

INSERT INTO `config` (`name`, `value`) VALUES ('FEATURE_RADIUS_MIKROTIK', 'disabled');

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'radius_attr_mikrotik', 'Vendor Specific: Mikrotik');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'radius_attr_mikrotik_about', 'The following attributes are specific to Mikrotik-OS based devices.');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'vendor_attr_about_check', 'The check vendor attributes are used to define what attributes must be sent to radius when authenticating as the user. Different operators have different functionality for configuring what options are provided.');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'vendor_attr_about_reply', 'The reply vendor attributes are used to define what attributes are returned by radius to the NAS device after authentication - typically information such as connection limits, options, settings, user preferences.');


--
-- Upgrades for new password configuration options
--

INSERT INTO `config` (`name`, `value`) VALUES ('AUTH_USERPASSWORD_TYPE', 'SSHA');

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'AUTH_PASSWORD_INFO', 'LDAPAuthManager provides various options for password formatting inside the LDAP directory server. From LDAP''s perspective, this is unimportant, however depending on your use of the user information, certain password options may be required. Typically for a *nix only deployment, you will wanted Salted SHA, but for a radius deployment doing PAP/CHAP, you will want to use one of the cleartext options.');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'config_credentials', 'Credentials and Authentication Configuration');



--
-- Upgrades for new LDAP zones
--


INSERT INTO `config` (`name`, `value`) VALUES ('FEATURE_ZONES', '0');


INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'zonename', 'Zone Name');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'zonepath', 'Zone DN');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'uniquemember', 'Zone Members');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'zone_view', 'View Zone');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'zone_members', 'Zone Members');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'zone_delete', 'Delete Zone');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'manage_zones', 'Manage Zones');


ALTER TABLE  `menu` ADD  `config` VARCHAR( 255 ) NOT NULL;

INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`, `config`) VALUES(NULL, 400, 'top', 'manage_zones', 'zone_management/zones.php', 2, 'FEATURE_ZONES');
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`, `config`) VALUES(NULL, 401, 'manage_zones', '', 'zone_management/zone-view.php', 2, 'FEATURE_ZONES');
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`, `config`) VALUES(NULL, 401, 'manage_zones', '', 'zone_management/zone-add.php', 2, 'FEATURE_ZONES');
INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`, `config`) VALUES(NULL, 401, 'manage_zones', '', 'zone_management/zone-delete.php', 2, 'FEATURE_ZONES');


--
-- Upgrades for new LDAP zones
--

INSERT INTO `config` (`name`, `value`) VALUES('FEATURE_LOGS_ENABLE', '1');
INSERT INTO `config` (`name`, `value`) VALUES('LOG_RETENTION_CHECKTIME', '0');
INSERT INTO `config` (`name`, `value`) VALUES('LOG_RETENTION_PERIOD', '0');

UPDATE `menu` SET config='FEATURE_LOGS_ENABLE' WHERE topic='menu_logs' LIMIT 1;



--
-- Upgrades for password policy
--

INSERT INTO `language` (`id` , `language` , `label` , `translation` ) VALUES ( NULL , 'en_us', 'config_password_policy', 'Password policy' );

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password_require_alpha', 'Password requires at least 1 alpha character');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password_require_numeric', 'Password requires at least 1 numeric character');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password_require_caps', 'Password requires at least 1 capitalised character');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password_require_special', 'Password requires at least 1 special character');

INSERT INTO `config` (`name` ,`value`) VALUES ('PASSWORD_LENGTH_MIN', '0');            
INSERT INTO `config` (`name` ,`value`) VALUES ('PASSWORD_REQUIRE_ALPHA', '0');           
INSERT INTO `config` (`name` ,`value`) VALUES ('PASSWORD_REQUIRE_NUMERIC', '0');          
INSERT INTO `config` (`name` ,`value`) VALUES ('PASSWORD_REQUIRE_CAPS', '0');            
INSERT INTO `config` (`name` ,`value`) VALUES ('PASSWORD_REQUIRE_SPECIAL', '0');          

--
-- Update the menu link to allow longer and external URLs
--

ALTER TABLE `menu` CHANGE `link` `link` VARCHAR( 255 ) NOT NULL 

--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20121102' WHERE name='SCHEMA_VERSION' LIMIT 1;



