<?php
/**********************************************************
    stats.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/14/05

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

if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
	$cur=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_SITE_STATS,"total_hits",array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"])));
	$cur++;
	$sql_db->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_SITE_STATS,array(array("total_hits",$cur,"time_last",time())),array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"])));
}
else if(!$error_log->get_status()){
	$cur=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_SITE_STATS,"total_hits",array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"])));
	if(!$cur){
		 $error_log->add_error(15,"sql"); 
	}
	$cur++;
	if(!$sql_db->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_SITE_STATS,array(array("total_hits",$cur,"time_last",$GLOBALS["SITE_DATA"]["SERVER_TIME"])),array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"])))){
	 	$error_log->add_error(15,"sql");  
	}
}

?>
