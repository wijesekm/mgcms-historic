--
-- Mandrigo Core SQL Dump
--

--
-- Table structure for table `mg_acl`
--
CREATE TABLE IF NOT EXISTS `mg_acl` (
  `acl_id` int(11) NOT NULL auto_increment,
  `acl_group` varchar(100) NOT NULL,
  `acl_page` varchar(100) NOT NULL,
  `acl_read` varchar(1) NOT NULL,
  `acl_modify` varchar(1) NOT NULL,
  `acl_write` varchar(1) NOT NULL,
  `acl_admin` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`)
);

--
-- Initial data for `mg_acl`
--
INSERT INTO `mg_acl` (`acl_id`, `acl_group`, `acl_page`, `acl_read`, `acl_modify`, `acl_write`, `acl_admin`) VALUES
(1, 'guest', '*', '+', '', '', 0),
(2, 'users', '*', '+', '', '', 0),
(3, 'admins', '*', '+', '+', '+', 1);

--
-- Table structure for table `mg_auth_log`
--
CREATE TABLE IF NOT EXISTS `mg_auth_log` (
  `auth_uid` varchar(100) NOT NULL,
  `auth_ip` varchar(20) NOT NULL,
  `auth_time` int(11) NOT NULL
);

--
-- Table structure for table `mg_config`
--
CREATE TABLE IF NOT EXISTS `mg_config` (
  `cfg_var` varchar(250) NOT NULL,
  `cfg_data` varchar(250) NOT NULL,
  PRIMARY KEY  (`cfg_var`)
);

--
-- Initial data for `mg_config`
--
INSERT INTO `mg_config` (`cfg_var`, `cfg_data`) VALUES
('ACCOUNT_TYPE', 'sqlact'),
('URI', 'mydomain.net'),
('URI_SSL', 'always'),
('COOKIE_PREFIX', 'mgauth_'),
('DEFAULT_ACT', 'guest'),
('ACCOUNT_DB', 'AuthDB'),
('ACCOUNT_TBL', 'mg_users'),
('ACCOUNTS_SESSION_TBL', 'mg_sessions'),
('TZ', 'America/Indianapolis'),
('URLTYPE', '2'),
('INDEX_NAME', 'index.php'),
('DEFAULT_LANGUAGE', 'en-us'),
('LANG_ALLOW_OVERRIDE', 'true'),
('TIME_FORMAT', 'h:i:s A T'),
('DATE_FORMAT', 'D F j, o'),
('NAME', 'My site');

--
-- Table structure for table `mg_groups`
--
CREATE TABLE IF NOT EXISTS `mg_groups` (
  `group_gid` varchar(100) NOT NULL,
  `group_members` mediumtext NOT NULL,
  PRIMARY KEY  (`group_gid`)
);

--
-- Initial data for `mg_groups`
--
INSERT INTO `mg_groups` (`group_gid`, `group_members`) VALUES
('guest', ';guest;'),
('users', '*'),
('admins', ';root;');

--
-- Table structure for table `mg_lang`
--
CREATE TABLE IF NOT EXISTS `mg_lang` (
  `lang_callname` varchar(150) NOT NULL,
  `lang_value` longtext NOT NULL,
  `lang_id` int(11) NOT NULL,
  PRIMARY KEY  (`lang_callname`)
) ;

--
-- Initial data for `mg_lang`
--
INSERT INTO `mg_lang` (`lang_callname`, `lang_value`, `lang_id`) VALUES
('E404_TITLE', '404 :: Page Not Found', 1),
('E404_CONTENT', '<h1>Page Not Found</h1>\r\n<p>The requested URL <b><!--TPL_CODE_START-->\r\nif("{SSL}"!=""){\r\n$retvar=''https://'';\r\n}\r\nelse{\r\n$retvar=''http://'';\r\n}\r\n$retvar.=''{SERVER_NAME}''.''{REQUEST_URI}'';\r\n<!--TPL_CODE_END--></b> was not found on this server. Try checking the sitemap or going back to the site index. If you think this is an error please contact the <a href="#" onclick="document.location=emailToJS(webmaster,dom);">Webmaster</a>.</p>\r\n<hr/>\r\n{SERVER_SIGNATURE}', 1),
('E403_TITLE', '403 :: Forbidden', 1),
('E403_CONTENT', '<h1>Forbidden</h1>\r\nYou do not have permission to access <b><!--TPL_CODE_START-->\r\nif("{SSL}"!=""){\r\n$retvar=''https://'';\r\n}\r\nelse{\r\n$retvar=''http://'';\r\n}\r\n$retvar.=''{SERVER_NAME}''.''{REQUEST_URI}'';\r\n<!--TPL_CODE_END--></b> on the webserver.  If you think this is an error please contact the <a href="#" onclick="document.location=emailToJS(webmaster,dom);">Webmaster</a>.</p>\r\n<hr/>\r\n{SERVER_SIGNATURE}', 1);

--
-- Table structure for table `mg_langsets`
--
CREATE TABLE IF NOT EXISTS `mg_langsets` (
  `lang_id` int(11) NOT NULL auto_increment,
  `lang_name` varchar(20) NOT NULL,
  `lang_encoding` varchar(20) NOT NULL,
  PRIMARY KEY  (`lang_id`),
  UNIQUE KEY `lang_name` (`lang_name`)
);

--
-- Initial data for `mg_langsets`
--
INSERT INTO `mg_langsets` (`lang_id`, `lang_name`, `lang_encoding`) VALUES
(1, 'en-us', 'UTF-8');

--
-- Table structure for table `mg_packages`
--
CREATE TABLE IF NOT EXISTS `mg_packages` (
  `pkg_name` varchar(100) NOT NULL,
  `pkg_type` varchar(20) NOT NULL default 'pkg',
  `pkg_mandrigoInc` varchar(250) NOT NULL,
  `pkg_maintainer` varchar(100) NOT NULL,
  `pkg_email` varchar(100) NOT NULL,
  `pkg_version` varchar(10) NOT NULL,
  `pkg_deps` varchar(250) default NULL,
  PRIMARY KEY  (`pkg_name`)
);

--
-- Table structure for table `mg_pages`
--
CREATE TABLE IF NOT EXISTS `mg_pages` (
  `page_path` varchar(255) NOT NULL,
  `page_name` varchar(100) NOT NULL,
  `page_packages` varchar(250) NOT NULL,
  `page_titlehook` varchar(50) NOT NULL,
  `page_contenthooks` varchar(250) NOT NULL,
  `page_varhooks` varchar(250) NOT NULL,
  `page_created` int(9) NOT NULL,
  `page_modified` int(9) NOT NULL,
  `page_createdby` varchar(50) default NULL,
  `page_modifiedby` varchar(50) default NULL,
  `page_root` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`page_path`)
);

--
-- Table structure for table `mg_vars`
--
CREATE TABLE IF NOT EXISTS `mg_vars` (
  `var_callname` varchar(250) NOT NULL,
  `var_getname` varchar(250) NOT NULL,
  `var_type` set('GET','POST','COOKIE') NOT NULL,
  `var_clean` varchar(250) NOT NULL,
  `var_default` varchar(250) NOT NULL,
  `var_tag` varchar(100) NOT NULL,
  PRIMARY KEY  (`var_callname`)
);

--
-- Initial data for `mg_vars`
--
INSERT INTO `mg_vars` (`var_callname`, `var_getname`, `var_type`, `var_clean`, `var_default`, `var_tag`) VALUES
('USER_NAME', 'mgauth_userid', 'COOKIE', 'username,1,0,1,0', 'guest', ''),
('USER_SESSION', 'mgauth_sessionid', 'COOKIE', 'id,1,0,1,0', '0', ''),
('PAGE', 'p', 'GET', 'file_path,1,0,1,0', 'index', ''),
('ACTION', 'a', 'GET', 'id,1,0,0,0', '', ''),
('PAGE_NUMBER', 'pn', 'GET', 'number,1,0,0,0', '0', '');
