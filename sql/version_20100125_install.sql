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
('SCHEMA_VERSION', '20100124'),
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
(300, 'en_us', 'configuration', 'Configuration');

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
-- Set Schema Version
--

UPDATE `config` SET `value` = '20100124' WHERE name='SCHEMA_VERSION' LIMIT 1;



