<?php
/**********************************************************
    elog.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 06/24/06

	Copyright (C) 2006  Kevin Wijesekera

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

//lang globals
$GLOBALS["ELOG"]["TITLE"]="Fatal Error";
$GLOBALS["ELOG"]["TITLE2"]="Error Log";
$GLOBALS["ELOG"]["ZERO"]="Fatal Error #0: The error logging script is either missing or in the wrong directory.  Please contact the webmaster to alert him/her of this issue";
$GLOBALS["ELOG"]["ONE"]="Fatal Error #1: The error logging script could not load the correct log ini files.  Please contact the webmaster to alert him/her of this issue";
$GLOBALS["ELOG"]["TWO"]="Fatal Error #3: The error logging script could not load the template file";
$GLOBALS["ELOG"]["THREE"]="Fatal Error #1: The Error Log could not write the errors to the log.  Please contact the webmaster to alert him/her of this issue";
$GLOBALS["ELOG"]["PERMISSION"]="You are not authorized to view this page.";

//html globals
$GLOBALS["ELOG"]["HTMLHEAD"]="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n\n<html>\n<head>\n\t<title>";
$GLOBALS["ELOG"]["HTMLBODY"]="\n\t</title>\n</head>\n<body>\n";
$GLOBALS["ELOG"]["HTMLEND"]="\n</body>\n</html>";
?>