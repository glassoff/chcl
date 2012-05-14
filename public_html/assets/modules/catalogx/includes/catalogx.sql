# MODx Database Script for New/Upgrade Installations
# MODx was created By Raymond Irving - Nov 2004 
#
# Each sql command is separated by double lines \n\n 




CREATE TABLE IF NOT EXISTS `modx_catalogx_keyword_xref` (
  `content_id` int(11) NOT NULL default '0',
  `keyword_id` int(11) NOT NULL default '0',
  KEY `content_id` (`content_id`),
  KEY `keyword_id` (`keyword_id`)
) TYPE=MyISAM DEFAULT CHARSET=cp1251  COMMENT='Cross reference bewteen keywords and content';

CREATE TABLE IF NOT EXISTS `modx_site_catalogx` (
  `id` int(10) NOT NULL auto_increment,
  `type` varchar(20) NOT NULL default 'document',
  `contentType` varchar(50) NOT NULL default 'text/html',
  `pagetitle` varchar(255) NOT NULL default '',
  `longtitle` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `alias` varchar(255) default '',
  `link_attributes` varchar(255) NOT NULL default '',
  `published` int(1) NOT NULL default '0',
  `pub_date` int(20) NOT NULL default '0',
  `unpub_date` int(20) NOT NULL default '0',
  `parent` int(10) NOT NULL default '0',
  `isfolder` int(1) NOT NULL default '0',
  `introtext` text COMMENT 'Used to provide quick summary of the document',  
  `content` mediumtext,
  `richtext` tinyint(1) NOT NULL default '1',
  `template` int(10) NOT NULL default '1',
  `menuindex` int(10) NOT NULL default '0',
  `searchable` int(1) NOT NULL default '1',
  `cacheable` int(1) NOT NULL default '1',
  `createdby` int(10) NOT NULL default '0',
  `createdon` int(20) NOT NULL default '0',
  `editedby` int(10) NOT NULL default '0',
  `editedon` int(20) NOT NULL default '0',
  `deleted` int(1) NOT NULL default '0',
  `deletedon` int(20) NOT NULL default '0',
  `deletedby` int(10) NOT NULL default '0',
  `publishedon` int(20) NOT NULL default '0',
  `publishedby` int(10) NOT NULL default '0',
  `menutitle` varchar(255) NOT NULL DEFAULT '' COMMENT 'Menu title',
  `donthit` tinyint(1) NOT NULL default '0' COMMENT 'Disable page hit count',
  `haskeywords` tinyint(1) NOT NULL default '0' COMMENT 'has links to keywords',
  `hasmetatags` tinyint(1) NOT NULL default '0' COMMENT 'has links to meta tags',
  `privateweb` tinyint(1) NOT NULL default '0' COMMENT 'Private web document',
  `privatemgr` tinyint(1) NOT NULL default '0' COMMENT 'Private manager document',
  `content_dispo` tinyint(1) NOT NULL default '0' COMMENT '0-inline, 1-attachment',
  `hidemenu` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide document from menu',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `parent` (`parent`),
  KEY aliasidx (alias),
  FULLTEXT KEY `content_ft_idx` (`pagetitle`,`description`,`content`)
) TYPE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Contains the site document tree.';


CREATE TABLE IF NOT EXISTS `modx_site_catalogx_metatags` (
  `content_id` int(11) NOT NULL default '0',
  `metatag_id` int(11) NOT NULL default '0',
  KEY `content_id` (`content_id`),
  KEY `metatag_id` (`metatag_id`)
) TYPE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Reference table between meta tags and content';


CREATE TABLE IF NOT EXISTS `modx_site_tmplvar_catalogxvalues` (
	`id` int(11) NOT NULL auto_increment,
	`tmplvarid` int(10) NOT NULL default '0' COMMENT 'Template Variable id',
	`contentid` int(10) NOT NULL default '0' COMMENT 'Site Content Id',
	`value` text,
	PRIMARY KEY  (id),
	KEY idx_tmplvarid (tmplvarid),
	KEY idx_id (contentid)
) TYPE=MyISAM DEFAULT CHARSET=cp1251 COMMENT='Site Template Variables Content Values Link Table';




