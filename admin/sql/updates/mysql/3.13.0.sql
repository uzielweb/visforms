CREATE TABLE IF NOT EXISTS `#__vispdf` (
    id int(10) unsigned PRIMARY KEY NOT NULL AUTO_INCREMENT,
    asset_id int(10) unsigned DEFAULT '0' NOT NULL COMMENT 'FK to the #__assets table.',
    title varchar(255) DEFAULT '' NOT NULL,
    name varchar(255) DEFAULT '' NOT NULL,
    published tinyint(3) DEFAULT '0' NOT NULL,
    created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    created_by int(10) unsigned DEFAULT '0' NOT NULL,
    modified datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    modified_by int(10) unsigned DEFAULT '0' NOT NULL,
    checked_out int(10) unsigned DEFAULT '0' NOT NULL,
    checked_out_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    fid int(11),
    doc_template longtext,
    settings longtext,
    data longtext,
    document longtext,
    page longtext,
    hdr_template longtext,
    ftr_template longtext,
    image longtext,
    statements longtext,
    preview longtext,
	KEY `idx_published` (`published`),
	KEY `idx_createdby` (`created_by`),
	KEY `idx_checkout` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__viscreator`
(
    id int(10) unsigned PRIMARY KEY NOT NULL AUTO_INCREMENT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__visfields` ADD COLUMN `isfilterfield` tinyint(1) DEFAULT 0 NOT NULL;