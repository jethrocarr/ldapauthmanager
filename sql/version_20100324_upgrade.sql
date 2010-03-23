--
-- LDAPAUTHMANAGER APPLICATION
--
-- Upgrade SQL Commands
--


INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'filter_searchbox', 'Search Box');
INSERT INTO `language` (`id`, `language`, `label`, `translation`) VALUES (NULL, 'en_us', 'filter_hide_user_group_maps', 'Hide User-Groups');


--
-- Set Schema Version
--

UPDATE `config` SET `value` = '20100324' WHERE name='SCHEMA_VERSION' LIMIT 1;



