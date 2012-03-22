--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


--
-- Upgrades for new LDAP zones
--

INSERT INTO `config` (`name`, `value`) VALUES('FEATURE_LOGS_ENABLE', '1');
INSERT INTO `config` (`name`, `value`) VALUES('LOG_RETENTION_CHECKTIME', '0');
INSERT INTO `config` (`name`, `value`) VALUES('LOG_RETENTION_PERIOD', '0');

UPDATE `menu` SET config='FEATURE_LOGS_ENABLE' WHERE topic='menu_logs' LIMIT 1;


--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20120322' WHERE name='SCHEMA_VERSION' LIMIT 1;


