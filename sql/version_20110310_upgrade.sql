--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


--
-- Upgrades for new password configuration options
--

INSERT INTO `config` (`name`, `value`) VALUES ('AUTH_USERPASSWORD_TYPE', 'SSHA');

INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'AUTH_PASSWORD_INFO', 'LDAPAuthManager provides various options for password formatting inside the LDAP directory server. From LDAP''s perspective, this is unimportant, however depending on your use of the user information, certain password options may be required. Typically for a *nix only deployment, you will wanted Salted SHA, but for a radius deployment doing PAP/CHAP, you will want to use one of the cleartext options.');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'config_credentials', 'Credentials and Authentication Configuration');


--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20110310' WHERE name='SCHEMA_VERSION' LIMIT 1;


