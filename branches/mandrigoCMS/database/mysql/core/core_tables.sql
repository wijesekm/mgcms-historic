

-- 
-- Table structure for table `mg_accounts`
-- 

CREATE TABLE `mg_accounts` (
  `ac_id` int(11) NOT NULL auto_increment,
  `ac_username` varchar(40) NOT NULL,
  `ac_fullname` varchar(80) NOT NULL,
  `ac_email` varchar(80) NOT NULL,
  `ac_im` tinytext NOT NULL,
  `ac_website` varchar(80) NOT NULL,
  `ac_about` mediumtext NOT NULL,
  `ac_picture` varchar(150) NOT NULL,
  `ac_passwd` varchar(100) NOT NULL,
  `ac_status` set('A','D') NOT NULL default 'A',
  `ac_expires` int(11) NOT NULL default '0',
  `ac_lastlogin` int(11) NOT NULL default '0',
  `ac_lastip` varchar(40) NOT NULL default '000.000.000.000',
  `ac_lastpwdchg` int(11) NOT NULL default '0',
  `ac_lastchange` int(11) NOT NULL,
  `ac_created` int(11) NOT NULL,
  `ac_tz` varchar(4) NOT NULL default 'serv',
  `ac_dst` tinyint(1) NOT NULL default '1',
  `ac_lang` varchar(6) NOT NULL default 'en-US',
  `ac_groups` varchar(150) NOT NULL default '1',
  `ac_session` varchar(250) NOT NULL,
  PRIMARY KEY  (`ac_id`),
  UNIQUE KEY `ac_username` (`ac_username`)
);

-- 
-- Table structure for table `mg_acl`
-- 

CREATE TABLE `mg_acl` (
  `acl_id` int(11) NOT NULL auto_increment,
  `acl_groupid` int(11) NOT NULL,
  `acl_pageid` int(11) NOT NULL,
  `read_level` tinyint(4) NOT NULL,
  `aread_level` tinyint(1) NOT NULL default '0',
  `post_level` tinyint(4) NOT NULL,
  `edit_level` tinyint(4) NOT NULL,
  `config_level` tinyint(4) NOT NULL,
  `full_control` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`)
);

-- 
-- Table structure for table `mg_admin_pages`
-- 

CREATE TABLE `mg_admin_pages` (
  `pg_id` int(11) NOT NULL auto_increment,
  `pg_name` varchar(40) NOT NULL,
  `pg_fullname` varchar(100) NOT NULL,
  `pg_title` varchar(150) NOT NULL default '{CPAGE_FNAME} - Mandrigo Admin',
  `pg_vars` tinytext NOT NULL,
  `pg_hooks` varchar(40) NOT NULL,
  `pg_subpages` varchar(40) NOT NULL,
  `pg_parent` tinyint(5) NOT NULL,
  `pg_root` tinyint(1) NOT NULL default '0',
  `pg_datapath` varchar(100) NOT NULL,
  `pg_status` tinyint(1) NOT NULL default '1',
  `pg_readlevel` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`pg_id`)
);

-- 
-- Table structure for table `mg_config`
-- 

CREATE TABLE `mg_config` (
  `cfg_name` varchar(100) NOT NULL,
  `cfg_value` varchar(250) default NULL,
  PRIMARY KEY  (`cfg_name`)
);

-- 
-- Table structure for table `mg_groups`
-- 

CREATE TABLE `mg_groups` (
  `gp_id` int(11) NOT NULL auto_increment,
  `gp_name` varchar(80) NOT NULL,
  `gp_admins` varchar(250) NOT NULL,
  `gp_users` varchar(250) NOT NULL,
  `gp_about` mediumtext NOT NULL,
  `gp_picture` varchar(150) NOT NULL,
  PRIMARY KEY  (`gp_id`)
);

-- 
-- Table structure for table `mg_lang1`
-- 

CREATE TABLE `mg_lang1` (
  `lang_callname` varchar(20) NOT NULL,
  `lang_value` varchar(250) NOT NULL,
  `lang_corename` varchar(15) NOT NULL,
  `lang_appid` varchar(30) NOT NULL default '0',
  PRIMARY KEY  (`lang_callname`)
);

-- 
-- Table structure for table `mg_lang2`
-- 

CREATE TABLE `mg_lang2` (
  `lang_callname` varchar(20) NOT NULL,
  `lang_value` varchar(250) NOT NULL,
  `lang_corename` varchar(15) NOT NULL,
  `lang_appid` varchar(30) NOT NULL default '0',
  PRIMARY KEY  (`lang_callname`)
);

-- 
-- Table structure for table `mg_langsets`
-- 

CREATE TABLE `mg_langsets` (
  `lang_id` int(11) NOT NULL auto_increment,
  `lang_name` varchar(20) NOT NULL,
  `lang_type` set('L','H') NOT NULL default 'L',
  `lang_charset` varchar(10) NOT NULL,
  `lang_encoding` varchar(10) NOT NULL,
  PRIMARY KEY  (`lang_id`)
);

-- 
-- Table structure for table `mg_packages`
-- 

CREATE TABLE `mg_packages` (
  `pkg_id` int(11) NOT NULL default '0',
  `pkg_name` varchar(40) NOT NULL,
  `pkg_nlerror` int(11) NOT NULL,
  `pkg_ver` varchar(20) NOT NULL,
  `pkg_maintainer` varchar(60) default NULL,
  `pkg_email` varchar(150) default NULL,
  `pkg_web` varchar(150) default NULL,
  `pkg_status` set('E','D') NOT NULL default 'E',
  PRIMARY KEY  (`pkg_id`),
  UNIQUE KEY `pkg_name` (`pkg_name`)
);

-- 
-- Table structure for table `mg_pages`
-- 

CREATE TABLE `mg_pages` (
  `pg_id` int(11) NOT NULL auto_increment,
  `pg_name` varchar(40) NOT NULL,
  `pg_fullname` varchar(100) NOT NULL,
  `pg_title` varchar(150) NOT NULL default '{CPAGE_FNAME} - {SITE_NAME}',
  `pg_vars` tinytext NOT NULL,
  `pg_hooks` varchar(40) NOT NULL,
  `pg_subpages` varchar(40) NOT NULL,
  `pg_parent` tinyint(5) NOT NULL,
  `pg_root` tinyint(1) NOT NULL default '0',
  `pg_datapath` varchar(100) NOT NULL,
  `pg_status` tinyint(1) NOT NULL default '1',
  `pg_readlevel` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`pg_id`)
) ;

-- 
-- Table structure for table `mg_server_globals`
-- 

CREATE TABLE `mg_server_globals` (
  `var_name` varchar(40) NOT NULL,
  `var_getnames` varchar(40) NOT NULL,
  `var_corename` varchar(20) NOT NULL default 'mg_display',
  `var_appid` varchar(30) NOT NULL default '0',
  `var_protocols` varchar(80) NOT NULL default 'http_get',
  `var_cleanfuncts` varchar(120) NOT NULL,
  `var_defaults` varchar(100) NOT NULL default '0',
  PRIMARY KEY  (`var_name`)
);

-- 
-- Table structure for table `mg_stats_hits`
-- 

CREATE TABLE `mg_stats_hits` (
  `page_id` int(11) NOT NULL,
  `hits` int(11) NOT NULL,
  `last_hit` int(11) NOT NULL,
  PRIMARY KEY  (`page_id`)
);

-- 
-- Table structure for table `mg_stats_ips`
-- 

CREATE TABLE `mg_stats_ips` (
  `ip` varchar(20) NOT NULL default '000.000.000.000',
  `hits` int(11) NOT NULL,
  `last_hit` int(11) NOT NULL,
  PRIMARY KEY  (`ip`)
);

-- 
-- Table structure for table `mg_stats_uagents`
-- 

CREATE TABLE `mg_stats_uagents` (
  `agent` varchar(250) NOT NULL,
  `hits` int(11) NOT NULL,
  `last_hit` int(11) NOT NULL,
  PRIMARY KEY  (`agent`)
);

-- 
-- Table structure for table `mg_captcha`
-- 

CREATE TABLE `mg_captcha` (
  `ca_id` varchar(250) NOT NULL default '',
  `ca_string` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`ca_id`)
)

-- 
-- Table structure for table `mg_captchad`
-- 

CREATE TABLE `mg_captchad` (
  `page_id` int(11) NOT NULL default '0',
  `part_id` int(11) NOT NULL default '0',
  `name` varchar(10) NOT NULL default '',
  `chars` tinyint(4) NOT NULL default '5',
  `nb_noise` tinyint(4) NOT NULL default '30',
  `minsize` tinyint(4) NOT NULL default '20',
  `maxsize` tinyint(4) NOT NULL default '30',
  `maxrotation` tinyint(4) NOT NULL default '20',
  `jpgquality` tinyint(4) NOT NULL default '80',
  `websafecolors` tinyint(1) NOT NULL default '0',
  `ttf_range` varchar(250) NOT NULL default 'antelope.ttf;epilog.ttf;arialbd.ttf;britannica.ttf'
);
