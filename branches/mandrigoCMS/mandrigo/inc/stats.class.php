<?php
/**********************************************************
    stats.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/28/07

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

class stats{
	
	function stats(){}
	
	function st_reghit(){
		$hits=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_STATS_HITS,"hits",array(array("page_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"])));
		
		if(!$hits){
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_STATS_HITS,array($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"],1,$GLOBALS["MANDRIGO"]["SITE"]["TIME"]),array("page_id","hits","last_hit"))){
				return false;												
			}
		}
		else{
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_STATS_HITS,array(array("hits",$hits+1),array("last_hit",$GLOBALS["MANDRIGO"]["SITE"]["TIME"])),array(array("page_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"])))){
				return false;												
			}		
		}
		return true;
	}	
	function st_regip(){
		$hits=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_STATS_IPS,"hits",array(array("ip","=",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"])));
		
		if(!$hits){
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_STATS_IPS,array($GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"],1,$GLOBALS["MANDRIGO"]["SITE"]["TIME"]),array("ip","hits","last_hit"))){
				return false;												
			}
		}
		else{
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_STATS_IPS,array(array("hits",$hits+1),array("last_hit",$GLOBALS["MANDRIGO"]["SITE"]["TIME"])),array(array("ip","=",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"])))){
				return false;												
			}		
		}
		return true;
	}
	function st_reguagent(){
		$hits=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_STATS_USERAGENTS,"hits",array(array("agent","=",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UAGENT"])));
		
		if(!$hits){
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_STATS_USERAGENTS,array($GLOBALS["MANDRIGO"]["CURRENTUSER"]["UAGENT"],1,$GLOBALS["MANDRIGO"]["SITE"]["TIME"]),array("agent","hits","last_hit"))){
				return false;												
			}
		}
		else{
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_STATS_USERAGENTS,array(array("hits",$hits+1),array("last_hit",$GLOBALS["MANDRIGO"]["SITE"]["TIME"])),array(array("agent","=",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UAGENT"])))){
				return false;												
			}		
		}
		return true;	
	}
}