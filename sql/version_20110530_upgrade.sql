--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


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
-- Set Schema Version
--

UPDATE `config` SET `value` = '20110530' WHERE name='SCHEMA_VERSION' LIMIT 1;


