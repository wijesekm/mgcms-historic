<?php
/**********************************************************
    display.pkg.php
    newssum ver 1.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/16/05

	Copyright (C) 2006 Kevin Wijesekera

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


class newssum_display{

	var $news_db;
	var $cfg;
	var $tpl;
	var $pparse_vars;

    function newssum_display(&$db){
		$this->news_db=$db;
    }
    function display($i){
		if($this->cfg["skin_type"]==SKIN_MINI){
			return $this->display_mini($i);
		}
		else{
			return $this->display_full($i);
		}
	}
    function display_full($i){
  		if(!$this->load($i),true){
			return false;
		} 	
  	}
  	function display_mini($i){
  		if(!$this->load($i),SKIN_MINI){
			return false;
		} 	
		$feeds=$this->news_db->db_numrows(TABLE_PREFIX.TABLE_NEWSSUM."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$i,"");
		$feed_data="";
		if($feeds==0){
			$feed_data=$GLOBALS["LANGUAGE"]["NOFEEDS"];
		}
		else{
		  	$j=0;
			for($i=0;$j<$feeds;$i++){
				if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWSSUM."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$i,"",array(array("feed_id","=",$j)))){
					$j++;
					if(ereg("sql://",$sql_result["feed_address"])){
					  	$sql_result["feed_address"]=ereg_replace("sql://","",$sql_result["feed_address"]);
						if(ereg("@localhost",$sql_result["feed_address"])){
							$sql_result["feed_address"]=ereg_replace("@localhost","",$sql_result["feed_address"]); 
							$tmp=explode(":",$sql_result["feed_address"]);
							$id=$this->get_sqlfeed($tmp[0],$tmp[1]);
							for($k=0;$k<$sql_result["num_posts"];$k++){
								$sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS."_".$tmp[0]."_",$tmp[1],"",array(array("post_id","=",$id[$i])));	
								$tpl=new template()
								$tpl->load("",$this->tpl->return_template(1));
				                $parse_vars = array(
				                        "NEWS_MAIN_TITLE",$sql_result["post_title"]
				                        ,"NEWS_MAIN_DATE",date($this->cfg["date_struct"],$sql_result["post_time"])
				                        ,"NEWS_MAIN_TIME",$this->gen_comlink($sql_result["post_time"],$tmp[0],$tmp[1])
				                        ,"NEWS_MAIN_USER",$this->gen_user($sql_result["post_author"])
				                        ,"NEWS_MAIN_CONTENT",$sql_result["post_content"]);
								$tpl->pparse($parse_vars);
								$feed_data.=$tpl->return_template();
							} 
						}
					}		
				}	
			}
		}
		$this->pparse_vars=array("NEWSUM_POSTS",$feed_data);
		$tpl=new template();
		$tpl->load("",$this->tpl->return_template(0));
		
	}
	function return_vars(){
		return $this->pparse_vars;
	}
	function gen_comlink($time,$local,$page_id,$post_id){
		if(!$local){
			return date($this->cfg["time_struct"],$time);
		}
		return $this->gen_link_internal($page_id,)
		$this->gen_link_internal($page_id,$post_id,date($this->cfg["time_struct"],$time),"id");
	}
    function gen_user($uid){
        if(!@$sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$uid)))){
            return false;
        }
        return $this->gen_link_internal($GLOBALS["SITE_DATA"]["PROFILE_PAGE"],"u".$uid,$sql_result["user_name"],"id");
    }
    function gen_link_internal($page,$uid,$name,$form="n"){
        if($GLOBALS["SITE_DATA"]["URL_FORMAT"]==1){
            $link = $GLOBALS["SITE_DATA"]["SITE_URL"].$GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."/p/".$page."/$form/".$uid;
        }
        else{
            $link = $GLOBALS["SITE_DATA"]["SITE_URL"].$GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."?p=".$page."&amp;$form=".$uid;
        }	
        return ereg_replace("{ATTRIB}","href=\"$link\"",$GLOBALS["HTML"]["A"]).$name.$GLOBALS["HTML"]["A!"];
	}
	function get_sqlfeed($page_id,$part_id){
	  	$i=$this->news_db->db_numrows(TABLE_PREFIX.TABLE_NEWS."_".$page_id."_".$part_id,"");
        if($i==0){
			return false;
		}
        $j = 0;
        $post_array=array();
        while($i>0){
	        if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS."_".$page_id."_".$part_id,"",array(array("post_id","=",$j)))){
				$post_array[$i]=$sql_result["post_id"];
	            $i--;
	        }
            $j++;
        }
        return $post_array;		
	}
	function get_rssfeed(){
	  
	}
	function load($i,$template){
		if(!$sql_result=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS_SUM_DATA,"",array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"],DB_AND),array("part_id","=",$i)))){
            return false;
        }
        $this->cfg["num_per_page"]=$sql_result["num_per_page"];
        $this->cfg["date_struct"]=$sql_result["date_struct"];
        $this->cfg["time_struct"]=$sql_result["time_struct"];
        $this->cfg["skin_type"]=$sql_result["skin_type"];
        $this->cfg["crop_length"=$sql_result["crop_length"];
        if($template==SKIN_MINI){
			$this->tpl=new template();
			if(!$this->tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].$GLOBALS["PAGE_DATA"]["DATAPATH"].$GLOBALS["PAGE_DATA"]["ID"]."_".$i.TPL_NEWSSUM_MINI.".".TPL_EXT,"","<!--NEWS_DELIM-->")){
				return false;
			}	
		}
		return true;
	}
}
?>
