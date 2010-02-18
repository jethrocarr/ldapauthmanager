--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


INSERT INTO `config` (`name`, `value`) VALUES ('FEATURE_RADIUS', 'disabled');

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'config_features', 'Feature Configuration');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'tbl_lnk_radius', 'radius attr');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'radius_attr', 'Standard Radius Attributes');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'radius_attr_vendor', 'Vendor-Specific Radius Attributes');

INSERT INTO `menu` (`id`, `priority`, `parent`, `topic`, `link`, `permid`) VALUES (NULL, '201', 'manage_users', '', 'user_management/user-radius.php', '2');


--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20100219' WHERE name='SCHEMA_VERSION' LIMIT 1;



