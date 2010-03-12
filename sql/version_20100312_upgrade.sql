--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


INSERT INTO `config` (`name`, `value`) VALUES ('FEATURE_RADIUS_MAXVENDOR', '10');


--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20100312' WHERE name='SCHEMA_VERSION' LIMIT 1;



