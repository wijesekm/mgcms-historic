<?php
/**********************************************************
    globals.pkg.php
    {package_name} ver {version}
	Last Edited By: {yourname}
	Date Last Edited: {date}

	Copyright (C) {year} {yourname}
	
	MandrigoCMS is Copyright (C) 2005-2007 the MandrigoCMS Group
	
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

//
//this file will contain basic globals and defines
//the way we would like you to format your globals is as follows:
//$GLOBALS['MANDRIGO']['PACKAGE_NAME']['VAR']
//where package name is the name of your package and var is the var
//
//defines should be in ALL CAPS
//
$GLOBALS['MANDRIGO']['PACKAGE_NAME']['VAR']='test';
define('PACKAGEABBR_VAR','test');

?>
