<?php
/**********************************************************
    constants.admin.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/16/07

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

define("TABLE_ADMIN_PAGES","admin_pages");

define("CFGTPL_PATH","/config_templates/");
define("EXT_TPL","extension.inc.".TPL_EXT);
define("CFG_TPL","config.ini.php.".TPL_EXT);
define("EXT_NAME","extension.inc");
define("CFG_NAME","config.ini.".PHP_EXT);

define("TPL_ADMINPATH","/admin/");
define("TPL_ADMIN","admin.".TPL_EXT);
