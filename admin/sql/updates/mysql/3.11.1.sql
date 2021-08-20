ALTER TABLE `#__visfields` ADD COLUMN `displayImgAsImgInList` tinyint(1) NOT NULL default 0;
ALTER TABLE `#__visfields` ADD COLUMN `displayImgAsImgInDetail` tinyint(1) NOT NULL default 0;
ALTER TABLE `#__visfields` ADD COLUMN `dataordering` int(11) NOT NULL default 0;
UPDATE `#__visfields` SET `dataordering` = `ordering`;