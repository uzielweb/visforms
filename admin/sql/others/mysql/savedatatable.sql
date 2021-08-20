(
`id` int(11) not null AUTO_INCREMENT,
`published` tinyint,
`created` datetime,
`created_by` int(11) NOT NULL default '0',
`checked_out` int(10) NOT NULL default '0',
`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
`ipaddress` TEXT NULL,
`mfd_id` int(11) NOT NULL default 0,
primary key (id)
)
ENGINE=InnoDB AUTO_INCREMENT=0 CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;