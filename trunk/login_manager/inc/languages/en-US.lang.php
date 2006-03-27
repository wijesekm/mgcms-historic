<?php
/**********************************************************
    english.lang.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/03/05

	Copyright (C) 2005  Kevin Wijesekera

    ##########################################################
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

	###########################################################

**********************************************************/

//
//To prevent direct script access
//
if(!defined("START_MANDRIGO")){
    die("<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>");
}

//
//Headers
//
header("Content-type: text/html; charset=utf-8");

//
// LANG Array
//
$GLOBALS["LANGUAGE"]["ENCODING"]="iso-8859-1";

$GLOBALS["LANGUAGE"]["INSTALLWARN"]="Mandrigo CMS is not setup.  Please run the installer or go to http://yourdomain/path/install/install.php for web based install.";
$GLOBALS["LANGUAGE"]["INSTALLTITLE"]="Install";
$GLOBALS["LANGUAGE"]["LOGIN"]="Login Manager";
$GLOBALS["LANGUAGE"]["ERROR"]="Fatal Error";
$GLOBALS["LANGUAGE"]["BAD_LOGIN"]="Username and password combination is incorrect";

//Error Logger
$GLOBALS["LANGUAGE"]["ETITLE"]="Fatal Error";
$GLOBALS["LANGUAGE"]["ETITLE2"]="Error Log";
$GLOBALS["LANGUAGE"]["EZERO"]="Fatal Error #0: The error logging script is either missing or in the wrong directory.  Please contact the webmaster to alert him/her of this issue";
$GLOBALS["LANGUAGE"]["EONE"]="Fatal Error #1: The error logging script could not load the correct log ini files.  Please contact the webmaster to alert him/her of this issue";
$GLOBALS["LANGUAGE"]["ETWO"]="Fatal Error #3: The error logging script could not load the template file";
$GLOBALS["LANGUAGE"]["ETHREE"]="Fatal Error #1: The Error Log could not write the errors to the log.  Please contact the webmaster to alert him/her of this issue";

?>
