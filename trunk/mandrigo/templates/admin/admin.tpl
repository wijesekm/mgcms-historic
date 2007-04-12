<!--MG_TEMPLATE_START_main-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License
-->

<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<title>{PAGE_TITLE}</title>
	
	<!--META Tags-->
	<meta name="author" content="{WEBMASTER_NAME}"/>
    <meta name="copyright" content="Copyright (c) 2005 - 2006 Mandrigo CMS Group"/>
    <meta name="description" content="" />
	<meta name="keywords" content="" />
    <meta name="robots" content="noindex,nofollow" />
    <meta name="generator" content="Mandrigo CMS {MG_VER}" />
    
	<!--Cascading Style Sheets-->
	<link href="http://mandrigo.org/site-code/css/core.css" rel="stylesheet" type="text/css" />
	<link href="http://mandrigo.org/site-code/css/links.css" rel="stylesheet" type="text/css" />
	<link href="http://mandrigo.org/site-code/css/style.css" rel="stylesheet" type="text/css" />
	
	<!--Syndication-->
	

</head>
<body>
<div id="header">
	<div id="logo">
		<img style="padding-top: 30px; padding-left: 50px;" src="http://mandrigo.org/images/core/logo.png" alt="Mandrigo CMS"/>
	</div>
	<div id="menu">
		<ul>
			<li><a href="{ADMIN_URL}{ADMIN_NAME}" accesskey="1" title="Home">Home</a></li>
			<li><a href="{ADMIN_URL}{ADMIN_NAME}/a/sconfig" accesskey="1" title="Home">Site Config</a></li>
			<li><a href="{ADMIN_URL}{ADMIN_NAME}/a/pconfig" accesskey="1" title="Home">Page Config</a></li>
			<li><a href="{ADMIN_URL}{ADMIN_NAME}/a/check" accesskey="1" title="Home">Check</a></li>
			<li><a href="{SITE_URL}" accesskey="1" title="Home" id="first" target="_blank">View Site</a></li>
			<li><a href="{LOGIN_URL}{LOGIN_NAME}/a/logout" accesskey="1" title="Home">Logout</a></li>
		</ul>
	</div>
</div>

<div id="splash">&nbsp;</div>
<div id="content">
	<div id="colOne">
		{CONTENT}
	</div>
	<div id="colTwo">
		<h4>Logged in As: {CUSER_LNAME}, {CUSER_FNAME} {CUSER_MNAME}</h4>
		<div style="text-align: center;">
		<a href="http://sf.net/projects/mandrigo"><img src="{IMG_URL}/sflogo.png" alt="SF Logo" /></a>
		</div>
	</div>

	<div style="clear: both;">&nbsp;</div>
</div>
<div id="footer">
	<p>Powered by Mandrigo CMS. Copyright &copy; 2005 - 2007 Mandrigo CMS Group.<br/>
	Design by <a href="http://freecsstemplates.org/">Free CSS Templates</a>.
	</p>

</div>
</body>
</html>


<!--MG_TEMPLATE_END_main-->
