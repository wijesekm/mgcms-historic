<?php
/**********************************************************
    setup.ini.php
    mga_packages ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/11/07

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

$pkg["name"]="mga_packages";
$pkg["version"]="0.7.0";
$pkg["maintainer"]="Kevin Wijesekera";
$pkg["email"]="k_wijesekera@yahoo.com";
$pkg["website"]="http://kevinwijesekera.net";

$pkg["enabled"]=true;
$pkg["no_load_error"]["id"]="1001";
$pkg["no_load_error"]["message"]="The mga_package package could not be loaded.";

//Do Not Edit Below This Line
if($pkg["enabled"]){
	$pkg["enabled"]="E";	
}
else{
	$pkg["enabled"]="D";	
}
