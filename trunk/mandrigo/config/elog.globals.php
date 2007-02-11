<?php
/**********************************************************
    elog.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 01/30/07

	Copyright (C) 2006-2007 the MandrigoCMS Group

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
    die($GLOBALS["MANDRIGO"]["CONFIG"]["DIE_STRING"]);
}


define("TPL_ERROR_LOG","error_log.".TPL_EXT);

//
//lang globals
//
$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"]="Fatal Error";
$GLOBALS["MANDRIGO"]["ELOG"]["TITLE2"]="Error Log";
$GLOBALS["MANDRIGO"]["ELOG"]["ZERO"]="Fatal Error #0: The error logging script is either missing or in the wrong directory.  Please contact the webmaster to alert him/her of this issue";
$GLOBALS["MANDRIGO"]["ELOG"]["ONE"]="Fatal Error #1: The error logging script could not load the correct log ini files.  Please contact the webmaster to alert him/her of this issue";
$GLOBALS["MANDRIGO"]["ELOG"]["TWO"]="Fatal Error #3: The error logging script could not load the template file";
$GLOBALS["MANDRIGO"]["ELOG"]["THREE"]="Fatal Error #1: The Error Log could not write the errors to the log.  Please contact the webmaster to alert him/her of this issue";
$GLOBALS["MANDRIGO"]["ELOG"]["PERMISSION"]="You are not authorized to view this page.";

//
//html globals
//
$GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"]="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n\n<html>\n<head>\n\t<title>";
$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"]="\n\t</title>\n</head>\n<body>\n";
$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]="\n</body>\n</html>";
$GLOBALS["MANDRIGO"]["ELOG"]["BR"]="<br/>";