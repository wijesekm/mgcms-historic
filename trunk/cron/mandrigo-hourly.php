#!/usr/bin/php
<?php
/**********************************************************
    mandrigo-hourly.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 2/27/06

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

#change this!!!
$web_path="";

#dont change below this!
define("START_MANDRIGO",true);
include($web_path."config/config.ini.php");
include($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."sql/".$sql_config["SQL_TYPE"].".class.php");
include($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini/constants.ini.php");
$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]=true;

$db=new db();
$db->connect($sql_config["SQL_HOST"],$sql_config["SQL_USER"],$sql_config["SQL_PASSWORD"],$sql_config["SQL_DATABASE"],$sql_config["SQL_PORT"]);

#tmp_dir cleaning
$db->query("TRUNCATE TABLE `".TABLE_PREFIX.TABLE_TEMP."`;");
?>
