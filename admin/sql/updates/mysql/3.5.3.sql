ALTER TABLE `#__visfields` ADD COLUMN `frontaccess` int(11) NOT NULL default 0;
INSERT INTO `#__visfields` (`frontaccess`) VALUES (1);