--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


TRUNCATE TABLE `language`;


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


--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20100203' WHERE name='SCHEMA_VERSION' LIMIT 1;



