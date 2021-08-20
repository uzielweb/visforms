ALTER TABLE `#__visforms` ADD COLUMN `redirecttoeditview` tinyint(1) NOT NULL default 0;
ALTER TABLE `#__visfields` MODIFY `defaultvalue` LONGTEXT;
ALTER TABLE `#__visforms` MODIFY `layoutsettings` LONGTEXT;