#!/usr/bin/php
<?php
/**********************************************************
    index.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/01/07

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

#change to be path to the webdir that mandrigo is installed under
$root_path="/var/public_html/external_web/htdocs/";

#dont change below this!
define("START_MANDRIGO",true);
include($root_path."config/extension.inc");
include($root_path."config/config.ini.".$php_ex);
include($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."sql/".$sql_config["SQL_TYPE"].".class.".$php_ex);
include($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."ini/constants.ini.".$php_ex);
$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]=true;

$db=new db();
$db->db_connect($sql_config["SQL_HOST"],$sql_config["SQL_PORT"],$sql_config["SQL_SOCKET"],$sql_config["SQL_USER"],
						$sql_config["SQL_PASSWORD"],$sql_config["SQL_DATABASE"],true,$sql_config["USE_SSL"],$sql_config["SSL"]);
#tmp_dir cleaning
$db->db_dbcommands(DB_TRUNCATE,"","",TABLE_PREFIX.TABLE_CAPTCHA);

#tmp image cleaning
function remove_dir($dir) {
   if(!$dh = @opendir($dir)) return;
   while (($obj = readdir($dh))) {
     if($obj=='.' || $obj=='..') continue;
     if (!@unlink($dir.'/'.$obj)) {
         remove_dir($dir.'/'.$obj);
     } else {
         $file_deleted++;
     }
   }
}
remove_dir($GLOBALS["MANDRIGO_CONFIG"]["IMG_PATH"].TMP_IMG);
?>
