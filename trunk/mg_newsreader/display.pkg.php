<?php
/**********************************************************
    display.pkg.php
    mg_newsreader ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/18/07

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

class mg_newsreader{
	
	var $tpl;
	var $cfg;
	var $id;
	
	function mg_newsreader($i){
		$this->tpl=new template();
		$this->id=$i;
	}
	function nr_display(){
		
	}
	function nr_readrss(){
		
	}
	function nr_readmg($page,$part){
		
	}
	function nr_readsql($config,$data,$url,$num_fetch=DB_ALL_ROWS){
		$db_new = & new db();
		if(!$db_new->db_connect($config["HOST"],$config["PORT"],$config["SOCKET"],$config["USER"],$config["PASS"],$config["DB"],true,$config["USSL"],$config["SSL"])){
			return false;
		}
		$posts=$db_new->db_fetcharray($config["table"],$data["post_id"].",".$data["post_timestamp"].",".$data["post"].",".$data["post_author"],"","ASSOC",$num_fetch);
		return $posts;
	}
}
