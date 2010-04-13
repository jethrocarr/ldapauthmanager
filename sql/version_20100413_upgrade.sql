--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL , 'en_us', 'gn', 'Given Name');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL , 'en_us', 'sn', 'Surname');

--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20100413' WHERE name='SCHEMA_VERSION' LIMIT 1;




