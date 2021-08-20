CREATE TABLE IF NOT EXISTS `#__visforms_spambot_attempts`	(
`id` int NOT NULL AUTO_INCREMENT,
`email` varchar(255),
`ip` varchar(15),
`engine` varchar(255),
`request` varchar(255),
`raw_return` varchar(255),
`parsed_return` varchar(255),
`attempt_date` varchar(255),
PRIMARY KEY(`id`)
) ENGINE=InnoDB CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;