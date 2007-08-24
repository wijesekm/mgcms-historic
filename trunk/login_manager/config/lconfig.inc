<?php
/**********************************************************
    lconfig.inc
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 08/22/07

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
//This should be changed to the path of your main mandrigo config directory
//
$main_cfg_path=ereg_replace("/login_manager/config/config.login.inc","",__FILE__)."/config/";

//
//DO NOT EDIT BELOW THIS
//
require($main_cfg_path."extension.inc");
require($main_cfg_path."config.ini.$php_ex");
require($main_cfg_path."elog.globals.$php_ex");

$main_cfg_path="";