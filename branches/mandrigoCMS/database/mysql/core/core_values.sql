-- 
-- Dumping data for table `mg_accounts`
-- 

INSERT INTO `mg_accounts` (`ac_id`, `ac_username`, `ac_fullname`, `ac_email`, `ac_im`, `ac_website`, `ac_about`, `ac_picture`, `ac_passwd`, `ac_status`, `ac_expires`, `ac_lastlogin`, `ac_lastip`, `ac_lastpwdchg`, `ac_lastchange`, `ac_created`, `ac_tz`, `ac_dst`, `ac_lang`, `ac_groups`, `ac_session`) VALUES 
(1, 'Guest', 'Mr.', 'M', 'Mysql', 'http://mysql.com', 'I am from the database. Yay', '', '', 'A', 0, 0, '000.000.000.000', 0, 0, 0, 'serv', 1, 'en-US', '1', '');


-- 
-- Dumping data for table `mg_acl`
-- 

INSERT INTO `mg_acl` (`acl_id`, `acl_groupid`, `acl_pageid`, `read_level`, `aread_level`, `post_level`, `edit_level`, `config_level`, `full_control`) VALUES 
(1, 1, 0, 1, 0, 0, 0, 0, 0);

-- 
-- Dumping data for table `mg_admin_pages`
-- 

INSERT INTO `mg_admin_pages` (`pg_id`, `pg_name`, `pg_fullname`, `pg_title`, `pg_vars`, `pg_hooks`, `pg_subpages`, `pg_parent`, `pg_root`, `pg_datapath`, `pg_status`, `pg_readlevel`) VALUES 
(1, 'main', 'Admin Home', '{APAGE_FNAME} - Mandrigo Admin', '', '-1;', '', 0, 1, 'admin/', 1, 1),
(2, 'packages', 'Package Manager', '{APAGE_FNAME} - Mandrigo Admin', '', '-2;', '', 0, 1, 'admin/', 1, 4);

-- 
-- Dumping data for table `mg_config`
-- 

INSERT INTO `mg_config` (`cfg_name`, `cfg_value`) VALUES 
('site_name', 'My Site'),
('site_url', '/'),
('img_url', '/images/mg_images/'),
('url_format', '1'),
('main_page', 'main'),
('form_mail_page', 'mail'),
('profile_page', 'profile'),
('bypass_code', ''),
('webmaster_name', ''),
('webmaster_email', '1'),
('last_updated', ''),
('mandrigo_ver', '0.6.0_dev'),
('page_type', '0'),
('index_name', 'index.php'),
('site_status', '1'),
('account_type', 'sql'),
('allow_userlang', '1'),
('stats_level', '3'),
('date_format', 'D F j, Y'),
('time_format', 'h:i:s a'),
('auth_type', 'sql'),
('login_path', '/'),
('login_domains', ''),
('login_secure', '0;0'),
('login_expires', '0'),
('login_rexpires', NULL),
('login_name', 'login_router.php'),
('login_url', '/login_manager/'),
('crypt_type', 'md5'),
('auto_reg', '1'),
('admin_name', 'admin.php'),
('default_group', '1'),
('admin_url', '/admin/'),
('main_apage', 'main'),
('update_server', 'package.mandrigo.org');

INSERT INTO `mg_groups` (`gp_id`, `gp_name`, `gp_admins`, `gp_users`, `gp_about`, `gp_picture`) VALUES 
(1, 'Guests', '', '1', '', '');

-- 
-- Dumping data for table `mg_lang1`
-- 

INSERT INTO `mg_lang1` (`lang_callname`, `lang_value`, `lang_corename`, `lang_appid`) VALUES 
('LI_TITLE', 'Login', 'mg_login', '0'),
('LI_BADCRED', 'Incorrect username or password!', 'mg_login', '0'),
('LI_NOREG', 'You''re Username and Password are correct but mandrigo couldnt not find a database record for you and Automatic Regestration is disabeled.  Please contact the webmaster of this site.', 'mg_login', '0'),
('LI_INERROR', 'Internal Error', 'mg_login', '0'),
('ADMIN_MAINTITLE', 'Mandrigo Admin Panel', 'mg_admin', '0'),
('ADMIN_CHECKTITLE', 'Check', 'mg_admin', '0'),
('PK_PACKAGEENABELED', 'Package Enabeled', 'mg_packages', '-2'),
('PK_PACKAGEDISABLED', 'Package Disabled', 'mg_packages', '-2'),
('PK_REMOVED', 'Package Removed!', 'mg_packages', '-2'),
('PK_DBREMOVEERROR', 'Could not remove tables! Installer will halt!', 'mg_packages', '-2'),
('PK_PACKAGEINSTALLED', 'Package Installed!', 'mg_packages', '-2'),
('PK_DBERROR', 'Could not add tables required by package! Installer will halt!', 'mg_packages', '-2'),
('PKG_LANGERROR', 'Could not update the language table! Installer will halt!', 'mg_packages', '-2'),
('PK_ENABLE', 'Enable', 'mg_packages', '-2'),
('PK_LOGERROR', 'Could not update the log files!  Installer will halt!', 'mg_packages', '-2'),
('PK_DISABLE', 'Disable', 'mg_packages', '-2'),
('PK_NEEDS_UPDATING', 'Needs Updating', 'mg_packages', '-2'),
('PK_UNKNOWN', 'Unknown', 'mg_packages', '-2'),
('PK_UP_TO_DATE', 'Up-To-Date', 'mg_packages', '-2');

-- 
-- Table structure for table `mg_lang2`
-- 

CREATE TABLE `mg_lang2` (
  `lang_callname` varchar(20) NOT NULL,
  `lang_value` varchar(250) NOT NULL,
  `lang_corename` varchar(15) NOT NULL,
  `lang_appid` varchar(30) NOT NULL default '0',
  PRIMARY KEY  (`lang_callname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `mg_lang2`
-- 

INSERT INTO `mg_lang2` (`lang_callname`, `lang_value`, `lang_corename`, `lang_appid`) VALUES 
('A', '<a {ATTRIB}>{VALUE}</a>', 'all', '0'),
('IMG', '<img {ATTRIB} />', 'all', '0'),
('OPT', '<option {VALUE}>{NAME}</option>	', 'mg_admin', '0'),
('ACRONYM', '<acronym {ATTRIB}>{VALUE}</acronym>', 'all', '0');

-- 
-- Dumping data for table `mg_langsets`
-- 

INSERT INTO `mg_langsets` (`lang_id`, `lang_name`, `lang_type`, `lang_charset`, `lang_encoding`) VALUES 
(1, 'en-US', 'L', 'utf-8', 'iso-8859-1'),
(2, 'xhtml_1_0_trans', 'H', '', '');

-- 
-- Dumping data for table `mg_packages`
-- 

INSERT INTO `mg_packages` (`pkg_id`, `pkg_name`, `pkg_nlerror`, `pkg_ver`, `pkg_maintainer`, `pkg_email`, `pkg_web`, `pkg_status`) VALUES 
(-1, 'mga_pcontent', 1000, '0.7.0', 'Kevin Wijesekera', 'k_wijesekera@yahoo.com', 'http://kevinwijesekera.net', 'E'),
(-2, 'mga_package', 1001, '0.7.0', 'Kevin Wijesekera', 'k_wijesekera@yahoo.com', 'http://kevinwijesekera.net', 'E'),
(-1337, 'mandrigo', 0, '0.7.0', 'Mandrigo CMS Team', NULL, 'http://mandrigo.org', 'E');

-- 
-- Dumping data for table `mg_pages`
-- 

INSERT INTO `mg_pages` (`pg_id`, `pg_name`, `pg_fullname`, `pg_title`, `pg_vars`, `pg_hooks`, `pg_subpages`, `pg_parent`, `pg_root`, `pg_datapath`, `pg_status`, `pg_readlevel`) VALUES 
(1, 'main', 'Index', '{CPAGE_FNAME} - {SITE_NAME}', '', '', '', 0, 1, '', 1, 2);

-- 
-- Dumping data for table `mg_server_globals`
-- 

INSERT INTO `mg_server_globals` (`var_name`, `var_getnames`, `var_corename`, `var_appid`, `var_protocols`, `var_cleanfuncts`, `var_defaults`) VALUES 
('page', 'p', 'all', '0', 'http_get', 'page', '$GLOBALS["MANDRIGO"]["SITE"]["MAIN_PAGE"]'),
('action', 'a', 'all', '0', 'http_get', 'page', '0'),
('cookie_session', 'mg_sesid', 'all', '0', 'http_cookie', 'sesid', '0'),
('cookie_user', 'mg_uid', 'all', '0', 'http_cookie', 'uid', '1'),
('APAGE', 'pa', 'mg_admin', '0', 'http_get', 'page', '$GLOBALS["MANDRIGO"]["SITE"]["MAIN_APAGE"]'),
('target', 't', 'all', '0', 'http_get', 'path', '/'),
('LI_USER', 'mg_user', 'mg_login', '0', 'http_post', 'username', '0'),
('LI_PASSWORD', 'mg_password', 'mg_login', '0', 'http_post', 'password', '0'),
('SECONDARYACTION', 'asub', 'mg_admin', '0', 'http_get', 'action', 'display'),
('package', 'pkg', 'mg_packages', '-2', 'http_get', 'page', '0');

