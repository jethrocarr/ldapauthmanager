--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


INSERT INTO `ldapauthmanager`.`menu` (`id` , `priority` , `parent` , `topic` , `link` , `permid`) VALUES (NULL , '301', 'manage_groups', '', 'group_management/group-radius.php', '2');


--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20100310' WHERE name='SCHEMA_VERSION' LIMIT 1;



