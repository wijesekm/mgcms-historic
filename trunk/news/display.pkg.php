<?php
/**********************************************************
    display.pkg.php
    news ver 1.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/09/05

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


class news_display{

	var $news_db;
	var $tpl;
	var $config;
	var $pparse_vars;

    function news_display(&$db){
		$this->news_db=$db;
    }
    
    function display_full($i){
    	if(!$this->config){
			return false;
		}  
		//
        //The start_post is equal to the current page number multiplied by the total number of posts per page plus one
        //The end post is equal to the current page number plus one multiplied by the number of posts per page
        //
        $start_post = ($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]*$this->config["num_per_page"])+1;
        $end_post = ($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]+1)*$this->config["num_per_page"];
   
        //total posts in the system
        $this->news_db->db_numrows(TABLE_PREFIX.TABLE_NEWS."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$i,"");
        if(!@$total_posts = $this->news_db->num_rows("SELECT * FROM `".TABLE_PREFIX.TABLE_NEWS."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$i."`;")){
            return false;
        }
        
        //prevents system from exceding the max number of posts
        if($end_post > $total_posts){
            $end_post = $total_posts;
        }
		//$start_post--;     
        //forms array of post id's from most current to oldest
        $id_array = $this->post_id_array($i,$total_posts);
        $last_time=0;
        $dd_date=true;
        $post_content="";

        //generates the content string
        while($start_post <= $end_post){
            if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$i,array(array("post_id","=",$id_array[$start_post])))){
                $dd_date=true;
				if($this->same_day($sql_result["post_time"],$last_time)&&$this->config["show_rep_time"]==0){
                    $dd_date=false;
                }
                $parse_vars = array(
                        "NEWS_MAIN_TITLE",$sql_result["post_title"]
                        ,"NEWS_MAIN_DATE",date($this->config["date_struct"],$sql_result["post_time"])
                        ,"NEWS_MAIN_TIME",$this->gen_link_internal($GLOBALS["HTTP_GET"]["PAGE"],$id_array[$start_post],date($this->config["time_struct"],$sql_result["post_time"]),"id")
                        ,"NEWS_MAIN_USER",$this->gen_user($sql_result["post_author"])
                        ,"NEWS_MAIN_CONTENT",$sql_result["post_content"]);
                $last_time = $sql_result["post_time"];
                $cur_tpl= new template();
            	$cur_tpl->load(false,$this->tpl->return_template(1).$this->tpl->return_template(2));
            	$cur_tpl->pparse($parse_vars,true,true,array($dd_date,true));
            	$post_content.=$cur_tpl->return_template();
            }
            $start_post++;
        } 
		$nav=$this->nav_gen($total_posts);
		$tpl=$this->tpl->return_template(0).$this->tpl->return_template(3).$this->tpl->return_template(4);
		if(!$nav){
			$tpl=$this->tpl->return_template(0).$this->tpl->return_template(3);
		} 
		$this->pparse_vars=array("NEWS_POSTS",$post_content
								,"NEWS_PAGE_LIST",$nav[0]
								,"NEWS_NAV",$nav[1]); 
		return $tpl;
	}
	function display_post($i){
    	if(!$this->config){
			return false;
		} 
		
		$template=$this->tpl->return_template();
		$post_content="";
		$post_add="";
		$post_error="";
		$act="";
		
		if($this->config["allow_comments"]){
	        $start_post = ($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]*$this->config["com_per_page"])+1;
	        $end_post = ($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]+1)*$this->config["com_per_page"];
	    	$total_posts=$this->news_db->db_numrows(TABLE_PREFIX.TABLE_NEWS_COMMENTS."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$i,array(array("post_id","=",$GLOBALS["HTTP_GET"]["ID"])));
	        if($end_post > $total_posts){
	            $end_post = $total_posts;
	        }
	        if($end_post==0){
				$start_post=0;
			}

	        $id_array = $this->post_id_array($i,$total_posts,TABLE_NEWS_COMMENTS,$GLOBALS["HTTP_GET"]["ID"]);
	        while($start_post <= $end_post){
	            if($sql_result1=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS_COMMENTS."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$i,"",array(array("post_id","=",$GLOBALS["HTTP_GET"]["ID"],DB_AND),array("com_id","=",$id_array[$start_post])))){
					if($sql_result1["user_id"]){
						$user=$this->gen_user($sql_result1["user_id"]);	
					}
					else{
						$user=$this->gen_link_internal($GLOBALS["SITE_DATA"]["FORM_MAIL_PAGE"],$sql_result1["user_email"],$sql_result1["user_name"],"mail");	
					}
					
					$parse_vars = array(
	                		 "NEWS_COM_DATE",date($this->config["date_struct"],$sql_result1["com_time"])
	                        ,"NEWS_COM_TIME",date($this->config["time_struct"],$sql_result1["com_time"])
	                        ,"NEWS_COM_COMMENT",$sql_result1["comment"]
							,"NEWS_COM_USER",$user);
	                $cur_tpl= new template();
	            	$cur_tpl->load(false,$this->tpl->return_template(2));
	            	$cur_tpl->pparse($parse_vars);
	            	$post_content.=$cur_tpl->return_template();
	            }
	            $start_post++;
	        }
			$template.=$this->tpl->return_template(1).$this->tpl->return_template(3);
			if($GLOBALS["SITE_DATA"]["URL_FORMAT"]==1){
            	$act = $GLOBALS["SITE_DATA"]["SITE_URL"].$GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."/p/".$GLOBALS["HTTP_GET"]["PAGE"]."/id/".$GLOBALS["HTTP_GET"]["ID"]."/a/p";
        	}
        	else{
            	$act = $GLOBALS["SITE_DATA"]["SITE_URL"].$GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."?p=".$GLOBALS["HTTP_GET"]["PAGE"]."&amp;id=".$GLOBALS["HTTP_GET"]["ID"]."&amp;a=p";
        	}
			if($this->config["allow_a_comments"]&&!$GLOBALS["USER_DATA"]["AUTHENTICATED"]){
				$post_add.=$this->tpl->return_template(5);	
			}				
			else if(!$this->config["allow_a_comments"]&&!$GLOBALS["USER_DATA"]["AUTHENTICATED"]){
				$post_add.=$this->tpl->return_template(4);	
			}
			else if(!$this->config["allow_a_comments"]&&$GLOBALS["USER_DATA"]["AUTHENTICATED"]){
				$post_add.=$this->tpl->return_template(6);	
			}
		}
		//gets the post from the database
		if(!$sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$i,"",array(array("post_id","=",$GLOBALS["HTTP_GET"]["ID"])))){
			return $GLOBALS["LANGUAGE"]["NO_POST"];  
		}
		if(!$post_content){
			$post_error=$GLOBALS["LANGUAGE"]["NO_COM"].$GLOBALS["HTML"]["BR"].$GLOBALS["HTML"]["BR"];
		}
		$this->pparse_vars=array("NEWS_MAIN_DATE",date($this->config["date_struct"],$sql_result["post_time"])
								,"NEWS_MAIN_TITLE",$sql_result["post_title"]
								,"NEWS_MAIN_TIME",date($this->config["time_struct"],$sql_result["post_time"])
								,"NEWS_MAIN_USER",$this->gen_user($sql_result["post_author"])
								,"NEWS_MAIN_CONTENT",$sql_result["post_content"]
								,"NEWS_COM",$post_content
								,"NEWS_COM_ERROR",$post_error
								,"NEWS_COM_ADD_COMMENT",$post_add
								,"NEWS_COM_ACTION",$act);
		return $template;			
	}
	function return_vars(){
		return $this->pparse_vars;
	}
	function post_id_array($part_id,$total_posts,$table=TABLE_NEWS,$post_id=""){
        $i = $total_posts;
        if($i==0){
			return false;
		}
        $j = 0;
        $post_id_array=array();
        while($i>0){
          	if(!$post_id){
	            if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.$table."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$part_id,"",array(array("post_id","=",$j)))){
					$post_id_array[$i] = $j;
	                $i--;
	            }
	        }
	        else{     	
	        	if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.$table."_".$GLOBALS["PAGE_DATA"]["ID"]."_".$part_id,"",array(array("post_id","=",$post_id,DB_AND),array("com_id","=",$j)))){
					$post_id_array[$i] = $j;
	                $i--;
	            }			
			}
            $j++;
        }
        return $post_id_array;
    }
    function nav_gen($total_length){
        $nav[0]="";
        $nav[1]="";
        if($total_length==0){
            $pages = 0;
        }
        else{
            $pages = ceil(($total_length / ($this->config["num_per_page"])));
        }
        $pages-=1;
        if($pages!=0){
            $i = 0;
            while($i <= $pages){
              	$nav[0].=$this->gen_link_internal($GLOBALS["HTTP_GET"]["PAGE"],$i,($i+1));
     		    $i++;
                if($i <= $pages){
                    $nav[0].=",";
                }
            }
            if($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]==0){
              	$nav[1].=$this->gen_link_internal($GLOBALS["HTTP_GET"]["PAGE"],($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]+1),"Next");
            }
            else if($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]==$pages){
              	$nav[1].=$this->gen_link_internal($GLOBALS["HTTP_GET"]["PAGE"],($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]-1),"Prev");
            }
            else{
              	$nav[1].=$this->gen_link_internal($GLOBALS["HTTP_GET"]["PAGE"],($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]+1),"Next");
              	$nav[1].=$this->gen_link_internal($GLOBALS["HTTP_GET"]["PAGE"],($GLOBALS["HTTP_GET"]["PAGE_NUMBER"]-1),"Prev");
            }
        }
        else{
        	return false;
        }
        return $nav;
    }
    function same_day($cur_time,$last_time){
        if($last_time == 0){
            return false;
        }
        if(date("d",$cur_time)==date("d",$last_time)){
            return true;
        }
        return false;
    }
    function gen_user($uid){
        if(!@$sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$uid)))){
            return false;
        }
        return $this->gen_link_internal($GLOBALS["SITE_DATA"]["PROFILE_PAGE"],"u".$uid,$sql_result["user_name"],"id");
    }
   	function pad_end_line($str){
        return ereg_replace("\n","<br/>",$str);
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
	function load($i,$type){
	  
		if(!$sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS_DATA,"",array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"],DB_AND),array("part_id","=",$i)))){
            return false;
        }
        $this->config["num_per_page"]=$sql_result["num_per_page"];
        $this->config["date_struct"]=$sql_result["date_struct"];
        $this->config["time_struct"]=$sql_result["time_struct"];
        $this->config["allow_comments"]=$sql_result["allow_comments"];
        $this->config["allow_a_comments"]=$sql_result["allow_a_comments"];
        $this->config["show_rep_time"]=$sql_result["show_rep_time"];
        $this->config["com_per_page"]=$sql_result["com_per_page"];
        if($this->config["num_per_page"]==0){
			return false;
		}
		if($this->config["com_per_page"]==0&&$this->config["allow_comments"]){
			return false;
		}
        
        $this->tpl=new template();
        if(!$this->tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].$GLOBALS["PAGE_DATA"]["DATAPATH"].$GLOBALS["PAGE_DATA"]["ID"]."_".$i."_$type.".TPL_EXT,"","<!--NEWS_DELIM-->")){
			return false;
		}
        return true;
	}
}
?>
