--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


--
-- Upgrades for password policy
--

INSERT INTO `language` (`id` , `language` , `label` , `translation` ) VALUES ( NULL , 'en_us', 'config_password_policy', 'Password policy' );

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password_require_alpha', 'Password requires at least 1 alpha character');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password_require_numeric', 'Password requires at least 1 numeric character');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'password_require_caps', 'Password requires at least 1 capitaliised character');
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

