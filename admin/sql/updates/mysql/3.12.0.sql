ALTER TABLE `#__visfields` ADD COLUMN `customlabelforsummarypage` text;
ALTER TABLE `#__visfields` ADD COLUMN `gridSizes` text;
ALTER TABLE `#__visfields` ADD COLUMN `controlGroupCSSclass` text;
CREATE TABLE IF NOT EXISTS `#__visforms_utf8_conversion` (
  `converted` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
INSERT INTO `#__visforms_utf8_conversion` (`converted`) VALUES (0);
CREATE TABLE IF NOT EXISTS `#__visforms_lowest_compat_version` (
  `vfversion` CHAR(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
INSERT INTO `#__visforms_lowest_compat_version` (`vfversion`) VALUES ('');