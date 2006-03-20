<?php
/**********************************************************
    xhtml.lang.php
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

//General Structure Tags
$GLOBALS["HTML"]["DOCTYPE"]="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n\n";
$GLOBALS["HTML"]["HTML"]="<html>\n";
$GLOBALS["HTML"]["HTML!"]="</html>\n";
$GLOBALS["HTML"]["HEAD"]="<head>\n";
$GLOBALS["HTML"]["HEAD!"]="</head>\n";
$GLOBALS["HTML"]["BODY"]="<body>\n";
$GLOBALS["HTML"]["BODY!"]="</body>\n";

//Head Tags
$GLOBALS["HTML"]["TITLE"]="<title>";
$GLOBALS["HTML"]["TITLE!"]="</title>\n";
?>