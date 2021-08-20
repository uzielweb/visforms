ALTER TABLE `#__visfields` ADD COLUMN `allowurlparam` tinyint(4) NOT NULL default 0;
ALTER TABLE `#__visfields` ADD COLUMN `customtextposition` tinyint(4) NOT NULL default 0;
ALTER TABLE `#__visfields` ADD COLUMN `uniquevaluesonly` tinyint(4) NOT NULL default 0;
ALTER TABLE `#__visfields` ADD COLUMN `restrictions` text;
ALTER TABLE `#__visforms` ADD COLUMN `layoutsettings` text;
ALTER TABLE `#__visforms` ADD COLUMN `emailreceiptfrom` text;
ALTER TABLE `#__visforms` ADD COLUMN `emailreceiptfromname` text;
UPDATE `#__visforms` SET `emailreceiptfrom` = `emailfrom`;
UPDATE `#__visforms` SET `emailreceiptfromname` = `emailfromname`;