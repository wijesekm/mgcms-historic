<?php
/**********************************************************
    setup.ini.php
    mg_news ver 0.7.0
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

$pkg["name"]="mg_news";
$pkg["id"]=4;
$pkg["version"]="0.7.0";
$pkg["maintainer"]="Kevin Wijesekera";
$pkg["email"]="k_wijesekera@yahoo.com";
$pkg["website"]="http://kevinwijesekera.net";

$pkg["enabled"]=true;
$pkg["no_load_error"]="502";

$pkg["errors"]=array("sql"=>array(array("400","Could not access the news overview table.  This usually means the table is blank or missing.")),
					 "access"=>array(),
					 "core"=>array(),
					 "display"=>array(array("120","The mg_news package could not load the template for the current page hook."),array("502","The mg_news package could not be loaded.")),
					 "ldap"=>array());

$pkg["languages"]=array("en-US");

$pkg_language_install["en-US"]=array(array("NEWS_NO_POSTS","There are currently no posts."),
								array("NEWS_INV_POST","There is currently no post with the given post ID."),
								array("NEWS_OUTOFRANGE","This page has no posts on it."),
								array("NEWS_COMOFF","Comments Turned Off."),
								array("NEWS_BADCAP","Invalid Security Code."),
								array("NEWS_NOCONTENT","Nothing to Post."),
								array("NEWS_POSTED","Posted"));
$pkg["tables"]=array("news");

$pkg_table_install["news"]["struct"]=array(array("page_id","int","11","","0"),
									       array("part_id","tinyint","4","","0"),
										   array("posts_num","smallint","6","","5"),
										   array("com_num","smallint","6","","5"),
										   array("date_struct","varchar","15","","l F jS, Y"),
										   array("time_struct","varchar","15","","h:i:s a"),
										   array("allow_com","tinyint","1","","1"),
										   array("allow_acom","tinyint","1","","0"),
										   array("merge_sameday","tinyint","1","","1"),
										   array("feed_allow","tinyint","1","","1"),
										   array("use_captcha","tinyint","1","","1"),
										   array("feed_ttl","varchar","10","","60"),
										   array("feed_ud_freq","varchar","10","","1"),
										   array("nav0_delim","varchar","3","",","),
										   array("nav1_delim","varchar","3","","|"));
										   
$pkg_table_install["news"]["keys"]=array(array(DB_PRIMARY,"page_id"));
$pkg_table_install["news"]["records"]=array();

//Do Not Edit Below This Line
if($pkg["enabled"]){
	$pkg["enabled"]="E";	
}
else{
	$pkg["enabled"]="D";	
}
