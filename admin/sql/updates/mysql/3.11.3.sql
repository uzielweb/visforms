drop table if exists `#__visverificationcodes`;
create table `#__visverificationcodes` (
  id int(11) not null AUTO_INCREMENT,
  fid int(11) NOT NULL DEFAULT 0,
  created  datetime NOT NULL default '0000-00-00 00:00:00',
  email varchar(100) default ''  not null,
  code varchar(30) default ''    not null,
  KEY `idx_email` (`email`),
  KEY `idx_fid` (`fid`),
  primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;