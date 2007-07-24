<?php
/**********************************************************
    cache.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/24/07

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

class cache{
	
	function cache_wpage($page_contents){
		if($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["CACHE"]===false){
			return 3;
		}
		$cache_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_CACHE,"",array(array("pg_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"])));
		if(!$cache_data["pg_id"]){
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_CACHE,array($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]),array("pg_id"))){
				return 0;
			}
			$cache_data["pg_id"]=$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"];
			$cache_data["cache_time"]=0;
		}
		if($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["LASTUPDATED"]>$cache_data["cache_time"]){
			if($this->cache_write($page_contents,$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"].$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"])){
				$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_CACHE,array(array("cache_time",$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"])),array(array("pg_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"])));
				return true;	
			}
			return false;
		}
		return 2;
	}
	
	function cache_rpage(){
		if($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["CACHE"]===false){
			return 3;
		}
		$cache_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_CACHE,"",array(array("pg_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"])));
		if(!$cache_data["pg_id"]){
			return 2;
		}
		if($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["LASTUPDATED"]>$cache_data["cache_time"]){
			return 2;	
		}
		return $this->cache_read($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"].$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"]);
	}
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	
	
	function cache_write($cache,$cache_name){
	 	$file=$GLOBALS["MANDRIGO"]["CONFIG"]["CACHE_PATH"].$cache_name.CACHE_EXT;
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			if(!$f=fopen($file,"w")){
				return false;
			}
			if(!fwrite($f,$cache)){
				return false;
			}
		}
		else{
			if(!$f=@fopen($file,"w")){
				return false;
			}
			if(!(@fwrite($f,$cache))){
				return false;
			}
		}
		@fclose($f);
		return true;
	}
	function cache_read($cache_name){
	 	$cache="";
	 	$file=$GLOBALS["MANDRIGO"]["CONFIG"]["CACHE_PATH"].$cache_name.CACHE_EXT;
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			if(!$f=fopen($file,"r")){
				return false;
			}
		}
		else{
			if(!$f=@fopen($file,"r")){
				return false;
			}
		}
        while(!feof($f)){
            $cache.=fgets($f);
        }
        @fclose($f);
		return $cache;		
	}
}