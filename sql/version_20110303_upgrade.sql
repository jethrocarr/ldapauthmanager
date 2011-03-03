--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


--
-- Upgrades for new Mikrotik vendor-specific extensions
--


INSERT INTO `config` (`name`, `value`) VALUES ('FEATURE_RADIUS_MIKROTIK', 'disabled');

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'radius_attr_mikrotik', 'Vendor Specific: Mikrotik');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'radius_attr_mikrotik_about', 'The following attributes are specific to Mikrotik-OS based devices.');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'vendor_attr_about_check', 'The check vendor attributes are used to define what attributes must be sent to radius when authenticating as the user. Different operators have different functionality for configuring what options are provided.');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'vendor_attr_about_reply', 'The reply vendor attributes are used to define what attributes are returned by radius to the NAS device after authentication - typically information such as connection limits, options, settings, user preferences.');



--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20110303' WHERE name='SCHEMA_VERSION' LIMIT 1;


