INSERT INTO `#__action_logs_extensions` (`extension`) VALUES
('com_visforms');
INSERT INTO `#__action_log_config` (`type_title`, `type_alias`, `id_holder`, `title_holder`, `table_name`, `text_prefix`) VALUES
('visforms', 'com_visforms.visform', 'id' ,'title' , '#__visforms', 'PLG_ACTIONLOG_VISFORMS'),
('visfield', 'com_visforms.visfield', 'id' ,'id' , '#__visfields', 'PLG_ACTIONLOG_VISFORMS'),
('visdata', 'com_visforms.visdata', 'id' ,'id' , '#__visforms_', 'PLG_ACTIONLOG_VISFORMS');