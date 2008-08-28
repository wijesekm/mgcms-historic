--
-- Initial data for `mg_config`
--
INSERT INTO `mg_config` (`cfg_var`, `cfg_data`) VALUES
('DEFAULT_AUTH', 'sqlauth'),
('AUTH_OVERRIDE', 'true'),
('PASS_ENCODING', 'md5'),
('COOKIE_DOM', 'mydomain.net'),
('COOKIE_SECURE', '0'),
('COOKIE_PATH', '/'),
('COOKIE_EXPIRES_DEFAULT', '0'),
('COOKIE_EXPIRES_REMEMBER', '12960000'),
('AM_PROFILES_PRIVATE', '0'),
('AM_UPP', '15');

--
-- Initial data for `mg_lang`
--
INSERT INTO `mg_lang` (`lang_callname`, `lang_value`, `lang_id`) VALUES
('LM_BAD_ACCOUNT', '<p class="error">Account requested does not exist!</p>', 1),
('LM_BAD_PASSWORD', '<p class="error">Bad Password!</p>', 1),
('LM_INT_ERROR', '<p class="error">Internal Error</p>', 1),
('LM_BANNED', '<p class="error">You have been banned from this website by an administrator.</p>', 1);

--
-- Initial data for `mg_packages`
--
INSERT INTO `mg_packages` (`pkg_name`, `pkg_type`, `pkg_mandrigoInc`, `pkg_maintainer`, `pkg_email`, `pkg_version`, `pkg_deps`) VALUES
('login_manager', 'pkg', '', 'Kevin Wijesekera', 'webmaster@wijesekera-home.net', '0.1.0', 'auth;'),
('auth', 'abstract', '/classes/auth/', 'Kevin Wijesekera', 'webmaster@wijesekera-home.net', '0.1.0', NULL),
('sqlauth', 'class', '/classes/auth/', 'Kevin Wijesekera', 'webmaster@wijesekera-home.net', '0.1.0', 'auth;'),
('pamauth', 'class', '/classes/auth/', 'Kevin Wijesekera', 'webmaster@wijesekera-home.net', '0.1.0', 'auth;'),
('account_mgr', 'pkg', '', 'Kevin Wijesekera', 'webmaster@wijesekera-home.net', '0.1.0', NULL);

--
-- Initial data for `mg_pages`
--
INSERT INTO `mg_pages` (`page_path`, `page_name`, `page_packages`, `page_titlehook`, `page_contenthooks`, `page_varhooks`, `page_created`, `page_modified`, `page_createdby`, `page_modifiedby`, `page_root`) VALUES
('index', 'Login Manager', 'login_manager;', 'login_manager::lm_titleHook', 'login_manager::lm_displayHook;', 'login_manager::lm_varHook;', 1217703197, 1217703197, 'wijesekm', 'wijesekm', 1),
('actmgr', 'Account Manager', 'account_mgr;', 'account_mgr::am_titleHook', 'account_mgr::am_contentHook;', 'account_mgr::am_varHook;', 1219870617, 1219870617, 'wijesekm', 'wijesekm', 1);

--
-- Table structure for table `mg_sessions`
--
CREATE TABLE IF NOT EXISTS `mg_sessions` (
  `ses_uid` varchar(100) NOT NULL,
  `ses_sid` varchar(250) NOT NULL,
  `ses_starttime` int(11) NOT NULL,
  `ses_length` int(50) NOT NULL,
  PRIMARY KEY  (`ses_uid`)
);

--
-- Table structure for table `mg_users`
--
CREATE TABLE IF NOT EXISTS `mg_users` (
  `user_uid` varchar(100) NOT NULL,
  `user_fullname` varchar(100) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `user_website` varchar(100) default NULL,
  `user_im` tinytext,
  `user_about` mediumtext,
  `user_password` varchar(250) default NULL,
  `user_auth` varchar(40) default NULL,
  `user_lang` varchar(20) default NULL,
  `user_account_created` varchar(50) NOT NULL,
  `user_account_modified` varchar(50) NOT NULL,
  `user_banned` tinyint(1) NOT NULL default '0',
  `user_tz` varchar(10) default NULL,
  PRIMARY KEY  (`user_uid`)
);

--
-- Initial data for `mg_users`
--
INSERT INTO `mg_users` (`user_uid`, `user_fullname`, `user_email`, `user_website`, `user_im`, `user_about`, `user_password`, `user_auth`, `user_lang`, `user_account_created`, `user_account_modified`, `user_banned`, `user_tz`) VALUES
('guest', 'Guest;;User', 'nobody@mydomain.net', '', NULL, NULL, NULL, NULL, NULL, '1217530526', '1217530526', 0, NULL),
('root', 'Root;;User', 'root@mydomain.net', '', NULL, NULL, NULL, 'pamauth', NULL, '1217964405', '1217964405', 0, NULL);

--
-- Initial data for `mg_vars`
--
INSERT INTO `mg_vars` (`var_callname`, `var_getname`, `var_type`, `var_clean`, `var_default`, `var_tag`) VALUES
('LOGIN_NAME', 'username', 'POST', 'username,1,0,1,0', '', ''),
('LOGIN_PASSWORD', 'password', 'POST', ',1,0,1,0', '', ''),
('REMEMBER_SESSION', 'remember_session', 'POST', 'boolean,0,0,1,0', '0', ''),
('QUERY', 'q', 'GET', ',1,0,1,0', '', ''),
('TARGET', 't', 'GET', ',1,0,1,0', 'http:[SLASH][SLASH]www.mydomain.net[SLASH]', ''),
('UID', 'uid', 'GET', 'username,1,0,1,0', '', '');