<?php
/**********************************************************
    lang.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 06/19/06

	Copyright (C) 2006  Kevin Wijesekera

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

class lang{
  
	var $user_lang;
	var $sys_lang;
	var $lang_db;
	var $charset;
	var $encoding;
	  
	function lang($lang,$db){
		$this->sys_lang=$syslang;
		$this->lang_db=$db;
	}
	function lang_setuser($lang){
		$this->user_lang=$lang;
	}
	function lang_returnlang(){
		return (isset($this->user_lang))?$user_lang:$sys_lang;
	}
	function lang_getitem($identifier){
		$lang_id=$this->lang_getlangid();
		return $this->lang_db->db_fetchresult(TABLE_PREFIX.TABLE_LANG.$lang_id,"lang_value",array(array("lang_callname","=",$identifier)));
	}
	function lang_getlangid(){
	  	$langname=(isset($this->user_lang))?$user_lang:$sys_lang;
		if(!$lang_id=$this->lang_db->db_fetchresult(TABLE_PREFIX.TABLE_LANG_MAIN,"lang_id",array(array("lang_name","=",$langname)))){
			if(!$lang_id=$this->lang_db->db_fetchresult(TABLE_PREFIX.TABLE_LANG_MAIN,"lang_id",array(array("lang_name","=",$this->sys_lang)))){
				$lang_id=0;
			}			
		}
		return $lang_id;
	}
	function set_charset(){
		if(!$lang=$this->lang_db->db_fetcharray(TABLE_PREFIX.TABLE_LANG_MAIN,"",array(array("lang_name","=",$langname)))){
			if(!$lang=$this->lang_db->db_fetcharray(TABLE_PREFIX.TABLE_LANG_MAIN,"",array(array("lang_name","=",$this->sys_lang)))){
				if(!$lang=$this->lang_db->db_fetcharray(TABLE_PREFIX.TABLE_LANG_MAIN,"",array(array("lang_name","=",0)){
					return false;
				}
			}			
		}
		$this->charset=$lang["lang_charset"];
		$this->encoding=$lang["lang_encoding"];
		header("Content-type: text/html; charset=".$this->charset);
		return true;
	}
  
}
?>