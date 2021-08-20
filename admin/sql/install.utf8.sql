SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
drop table if exists #__visforms;

create table #__visforms
(
   id                           int(11) not null AUTO_INCREMENT,
   asset_id INTEGER UNSIGNED NOT NULL DEFAULT 0,
   name                         text,
   title                        text,
   checked_out 					int(10) NOT NULL default '0',
   checked_out_time datetime NOT NULL default '0000-00-00 00:00:00',
   description                  longtext,
   emailfrom                    text,
   emailfromname				text,
   emailto                      text,
   emailcc                      text,
   emailbcc                     text,
   subject						text,	
   created                      datetime NOT NULL default '0000-00-00 00:00:00',
   created_by                   int(11) NOT NULL default 0,
   hits                         int(11) NOT NULL default '0',
   published                    tinyint NOT NULL default 0,
   saveresult                   tinyint,
   emailresult                  tinyint,
   textresult                   longtext,
   redirecturl					text,
   spambotcheck                 tinyint(1) NOT NULL default '0',
   captcha                    	tinyint,
   uploadpath					text,
   maxfilesize					int,
   allowedextensions			text,
   savemode                       tinyint(1) NOT NULL default '0',
   poweredby                   	tinyint,
   emailreceipt                 tinyint,
   emailreceipttext             longtext,
   emailreceiptsubject			text,
   emailreceiptfrom             text,
   emailreceiptfromname         text,
   emailreceiptsettings			text,
   emailresulttext              longtext,
   emailresultsettings			text,
   editemailresultsettings              longtext,
   editemailreceiptsettings              longtext,
   fronttitle                   text,
   frontdescription             longtext,
   frontendsettings				text,
   access                       int(11) NOT NULL default '0',   
   language                     char(7) NOT NULL,  
   exportsettings               text,
   layoutsettings               longtext,
   spamprotection               text,
   captchaoptions               text,
   viscaptchaoptions            text,
  redirecttoeditview              tinyint(1) not null DEFAULT 0,
  subredirectsettings			text,
  savesettings			text,
   primary key (id)
) ENGINE=InnoDB AUTO_INCREMENT=0 CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

drop table if exists #__visfields;

create table #__visfields
(
   id                           int(11) not null AUTO_INCREMENT,
   fid                          int(11),
   asset_id INTEGER UNSIGNED NOT NULL DEFAULT 0,
   name                         text,
   label                  	    text,
   checked_out 					int(10) NOT NULL default '0',
   checked_out_time datetime NOT NULL default '0000-00-00 00:00:00',
   created                      datetime NOT NULL default '0000-00-00 00:00:00',
   created_by                   int(11) NOT NULL default '0',
   typefield                    text,
   defaultvalue					longtext,
   published                    tinyint NOT NULL default 0,
   ordering                     int(11) not null DEFAULT 0, 
   gridSizes                    TEXT,
   controlGroupCSSclass         TEXT,
   labelCSSclass				text,
   fieldCSSclass				text,
   bootstrap_size               tinyint(3) UNSIGNED NOT NULL default 0,
   customtext					text,
   frontdisplay					tinyint,
   frontaccess                  int(11) NOT NULL default '0',
   includefieldonexport         tinyint(4) not null DEFAULT 1,
   allowurlparam                tinyint(4) not null DEFAULT 0,
   customtextposition           tinyint(4) not null DEFAULT 0,
   uniquevaluesonly             tinyint(4) not null DEFAULT 0,
   restrictions                 text,
   editonlyfield                tinyint(1) NOT NULL default 0,
   addtoredirecturl             tinyint(1) NOT NULL default 0,
   rdtparamname 				        text,
   includeinresultmail          tinyint(1) NOT NULL default 1,   
   includeinreceiptmail         tinyint(1) NOT NULL default 1,
   useoptionvalueinplaceholder         tinyint(1) NOT NULL default 0,
   customlabelforsummarypage    text,
   customlabelformail text,
   customlabelforcsv text,
   fileexportformat tinyint(1) NOT NULL default 0,
   displayAsMapInList tinyint(1) NOT NULL default 0,
   displayAsMapInDetail tinyint(1) NOT NULL default 0,
   listMapHeight CHAR(10) NOT NULL DEFAULT '',
   detailMapHeight CHAR(10) NOT NULL DEFAULT '',
   listMapZoom INT (11) NOT NULL DEFAULT 8,
   detailMapZoom INT (11) NOT NULL DEFAULT 13,
   allowferadiussearch tinyint(1) NOT NULL default 0,
   distanceunit CHAR(10) NOT NULL DEFAULT 'km',
   useassearchfieldonly tinyint(1) NOT NULL default 0,
   displayImgAsImgInList tinyint(1) NOT NULL default 0,
   displayImgAsImgInDetail tinyint(1) NOT NULL default 0,
   dataordering int(11) not null DEFAULT 0,
   isfilterfield tinyint(1) DEFAULT 0 NOT NULL,
   fileattachmentname varchar(255) DEFAULT '' NOT NULL,
   primary key (id)
) ENGINE=InnoDB AUTO_INCREMENT=0 CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

drop table if exists #__visverificationcodes;
create table #__visverificationcodes (
  id  int(11) not null AUTO_INCREMENT,
  fid int(11) NOT NULL DEFAULT 0,
  created  datetime NOT NULL default '0000-00-00 00:00:00',
  email        varchar(100) default ''  not null,
  code         varchar(30) default ''    not null,
  KEY `idx_email` (`email`),
  KEY `idx_fid` (`fid`),
  primary key (id)
) ENGINE=InnoDB AUTO_INCREMENT=0 CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

drop table if exists #__visforms_utf8_conversion;
CREATE TABLE IF NOT EXISTS `#__visforms_utf8_conversion` (
  `converted` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
INSERT INTO `#__visforms_utf8_conversion` (`converted`) VALUES (0);

DROP TABLE IF EXISTS `#__visforms_lowest_compat_version`;
CREATE TABLE IF NOT EXISTS `#__visforms_lowest_compat_version` (
  `vfversion` CHAR(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__action_logs_extensions` (`extension`) VALUES
('com_visforms');
INSERT INTO `#__action_log_config` (`type_title`, `type_alias`, `id_holder`, `title_holder`, `table_name`, `text_prefix`) VALUES
('visforms', 'com_visforms.visform', 'id' ,'title' , '#__visforms', 'PLG_ACTIONLOG_VISFORMS'),
('visfield', 'com_visforms.visfield', 'id' ,'id' , '#__visfields', 'PLG_ACTIONLOG_VISFORMS'),
('visdata', 'com_visforms.visdata', 'id' ,'id' , '#__visforms_', 'PLG_ACTIONLOG_VISFORMS');

DROP TABLE IF EXISTS `#__vispdf`;
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

DROP TABLE IF EXISTS `#__viscreator`;
CREATE TABLE IF NOT EXISTS `#__viscreator`
(
    id int(10) unsigned PRIMARY KEY NOT NULL AUTO_INCREMENT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;