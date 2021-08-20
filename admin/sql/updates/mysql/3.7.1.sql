ALTER TABLE `#__visfields` ADD COLUMN `editonlyfield` tinyint(1) NOT NULL default 0;
ALTER TABLE `#__visfields` ADD COLUMN `addtoredirecturl` tinyint(1) NOT NULL default 0;
ALTER TABLE `#__visfields` ADD COLUMN `includeinresultmail` tinyint(1) NOT NULL default 1;
ALTER TABLE `#__visfields` ADD COLUMN `includeinreceiptmail` tinyint(1) NOT NULL default 1;