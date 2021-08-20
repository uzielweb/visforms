ALTER TABLE `#__visfields` ADD COLUMN `allowferadiussearch` tinyint(1) NOT NULL default 0;
ALTER TABLE `#__visfields` ADD COLUMN `distanceunit` char(10) NOT NULL default 'km';
ALTER TABLE `#__visfields` ADD COLUMN `useassearchfieldonly` tinyint(1) NOT NULL default 0;