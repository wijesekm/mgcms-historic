<?php
/**********************************************************
    hooks.class.php
    mg_news ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/02/07

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

@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."captcha.class.".PHP_EXT);

class news{


	//private vars
	var $tpl;
	var $config;
	var $pparse_vars;

	//Constructor
    function news($id,$type){
	  	$this->tpl=new template();
		if(!$this->config=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_NEWS,'',array(array('page_id','=',$GLOBALS['MANDRIGO']['CURRENTPAGE']['ID'],DB_AND),array('part_id','=',$id)))){
            $GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(400,'sql');
			return false;
        }
        
	    if($this->config['num_per_page']==0){
	        $this->config['num_per_page']=1;
		}
		if($this->config['com_per_page']==0&&$this->config['allow_comments']){
			$this->config['com_per_page']=1;
		}
		
        if($type==NEWS_SINGLE){
      		$file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['NAME'].'_'.$id.'.'.TPL_EXT;
	        if(!$this->tpl->tpl_load($file,"top")||!$this->tpl->tpl_load($file,"bot")||!$this->tpl->tpl_load($file,"synd")
			 ||!$this->tpl->tpl_load($file,"post")||!$this->tpl->tpl_load($file,"postdate")||!$this->tpl->tpl_load($file,"addcom")
			 ||!$this->tpl->tpl_load($file,"nav")||!$this->tpl->tpl_load($file,"nav0")||!$this->tpl->tpl_load($file,"nav1")){
				$GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(120,'display');
				return false;
			}
			if($this->config["allow_com"]){
		        if(!$this->tpl->tpl_load($file,"coms")||!$this->tpl->tpl_load($file,"com")){
					$GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(120,'display');
					return false;
				}				
			}
		}
		else if($type==FEED_RSS1){
		 	$file=$GLOBALS['MANDRIGO']['CONFIG']['PLUGIN_PATH'].PACKAGE_TEMPLATE_PATH.FEED_PATH.$type.'.'.TPL_EXT;;
			if(!$this->tpl->tpl_load($file,"feed")||!$this->tpl->tpl_load($file,"feeditem")||!$this->tpl->tpl_load($file,"feedoverview")){
				$GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(120,'display');
				return false;					
			}			
		}
		else if($type==FEED_RSS092||$type==FEED_RSS2||$type==FEED_ATOM){
		 	$file=$GLOBALS['MANDRIGO']['CONFIG']['PLUGIN_PATH'].PACKAGE_TEMPLATE_PATH.FEED_PATH.$type.'.'.TPL_EXT;;
			if(!$this->tpl->tpl_load($file,"feed")||!$this->tpl->tpl_load($file,"feeditem")){
				$GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(120,'display');
				return false;
			}			
		}
		else{
      		$file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['NAME'].'_'.$id.'.'.TPL_EXT;
			if(!$this->tpl->tpl_load($file,"top")||!$this->tpl->tpl_load($file,"synd")||!$this->tpl->tpl_load($file,"bot")
			 ||!$this->tpl->tpl_load($file,"posts")||!$this->tpl->tpl_load($file,"post")||!$this->tpl->tpl_load($file,"postdate")
			 ||!$this->tpl->tpl_load($file,"nav")||!$this->tpl->tpl_load($file,"nav0")||!$this->tpl->tpl_load($file,"nav1")){
				$GLOBALS['MANDRIGO']["ERROR_LOGGER"]->el_adderror(120,'display');
				return false;
			}	
		}
        return true;
    }

	    
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################
    
	
	//
	//public function nd_displayfull($id);
	//
	//Displays the main new page
	//
    function ne_display($id,$type){
    	if(!$this->config){
			return false;
		}  
		switch($type){
			case NEWS_SINGLE:
				$this->ne_genrss();
				if($GLOBALS["MANDRIGO"]["VARS"]["ACTION"]=="addcom"){	
					return $this->ne_addcomment($id);		
				}
				return $this->ne_displaypost($id);		
			break;
			case FEED_RSS1:
			case FEED_RSS092:
			case FEED_RSS2:
			case FEED_ATOM:
				return $this->ne_displayfeed($id);	
			break;
			default:
				$this->ne_genrss();
				return $this->ne_displayfull($id);
			break;
		};
	}
	
	function ne_displayfull($id){
    	if(!$this->config){
			return false;
		}  

		//
        //The start_post is equal to the current page number multiplied by the total number of posts per page plus one
        //The end post is equal to the current page number plus one multiplied by the number of posts per page
        //
        $start_post=($GLOBALS['MANDRIGO']['VARS']['PAGE_NUMBER']*$this->config['posts_num']);
        $end_post=($GLOBALS['MANDRIGO']['VARS']['PAGE_NUMBER']+1)*$this->config['posts_num']-1;
        //total posts in the system
        $total_posts=$GLOBALS["MANDRIGO"]["DB"]->db_numrows(TABLE_PREFIX.TABLE_NEWS.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$id,'');

		if($total_posts==0||!$total_posts){
		 	return $this->tpl->tpl_return("top").$GLOBALS["MANDRIGO"]["LANGUAGE"]["NEWS_NO_POSTS"].$this->tpl->tpl_return("bot");
		}
		if($start_post>$total_posts){
		 	return $this->tpl->tpl_return("top").$GLOBALS["MANDRIGO"]["LANGUAGE"]["NEWS_OUTOFRANGE"].$this->tpl->tpl_return("bot");		
		}
        //prevents system from exceding the max number of posts
        if($end_post>$total_posts){
            $end_post=$total_posts-1;
        }
		
		$length=$end_post-$start_post+1;
		
        //forms array of post id's from most current to oldest
        $id_array=array_slice($this->ne_postid($id,$total_posts),$start_post,$length);
		$soid=count($id_array);

        $filter=array();
        for($i=0;$i<$soid;$i++){
			if($i+1<$soid){
				$filter[$i]=array("post_id","=",$id_array[$i]["post_id"],DB_OR);	
			}
			else{
				$filter[$i]=array("post_id","=",$id_array[$i]["post_id"]);
			}
		}
		$filter[$soid]=array(DB_ORDERBY,"post_time","DESC");
		$posts=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_NEWS.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$id,"",$filter,"ASSOC",DB_ALL_ROWS);
        $soq=count($posts);
        $post_content='';
        for($i=0;$i<$soq;$i++){
			$tpl_date=new template();
			$tpl_date->tpl_load($this->tpl->tpl_return("postdate"),"postd",false);
			$tpl_post=new template();
			$tpl_post->tpl_load($this->tpl->tpl_return("post"),"posttpl",false);
			$tpl_date->tpl_parse(array("DATE",date($this->config["date_struct"],$posts[$i]["post_time"])));
			$date=$tpl_date->tpl_return();
			if($this->ne_sameday($posts[$i]["post_time"],$posts[$i-1]["post_time"])&&$this->config["merge_sameday"]){
				$date="";
			}
			$pauth=$this->ne_genuserlink($posts[$i]["post_author"]);
			$tpl_post->tpl_parse(array("POST_TITLE",(string)trim($posts[$i]["post_title"])
									  ,"POST_CONTENT",(string)trim($posts[$i]["post_content"])
									  ,"POST_AUTHOR",$pauth[0]
									  ,"POST_AUTHOR_URL",$pauth[1]
									  ,"TIME",date($this->config["time_struct"],$posts[$i]["post_time"])
									  ,"POST_DATE",$date
									  ,"POST_URL",$this->ne_genpostlink($posts[$i]["post_id"])
									  ,"COMMENT_COUNT",(string)$this->ne_postcomcount($posts[$i]["post_id"],$id)
									  ));
			$post_content.=$tpl_post->tpl_return("posttpl");
		}
		$this->tpl->tpl_parse(array("POSTS",$post_content,"NAV",$this->ne_navgen($total_posts)),"posts",1,false);
		return $this->tpl->tpl_return("top").$this->tpl->tpl_return("posts").$this->tpl->tpl_return("bot");
	}
	
	//
	//public function nd_displaypost($id,$etype=0,$error='');
	//
	//Displays an individual post
	//
	function ne_displaypost($id,$error=""){
    	if(!$this->config){
			return false;
		} 
		
		$content="";
		//gets and displays the post
		if(eregi("p",$GLOBALS["MANDRIGO"]["VARS"]["ID"])){
			$pid=(int)ereg_replace("p","",$GLOBALS["MANDRIGO"]["VARS"]["ID"]);
		}
		else{
			$pid=(int)$GLOBALS["MANDRIGO"]["VARS"]["ID"];
		}

		$post=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_NEWS.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$id,"",array(array("post_id","=",$pid)));
		
		if(!$post){
	 		return $this->tpl->tpl_return("top").$GLOBALS["MANDRIGO"]["LANGUAGE"]["NEWS_INV_POST"].$this->tpl->tpl_return("bot");
		}
		$tpl_date=new template();
		$tpl_date->tpl_load($this->tpl->tpl_return("postdate"),"postd",false);
		$tpl_post=new template();
		$tpl_post->tpl_load($this->tpl->tpl_return("post"),"posttpl",false);
		$tpl_date->tpl_parse(array("DATE",date($this->config["date_struct"],$post["post_time"])));
		$date=$tpl_date->tpl_return();
		$pauth=$this->ne_genuserlink($post["post_author"]);
		$tpl_post->tpl_parse(array("POST_TITLE",(string)trim($post["post_title"])
								  ,"POST_CONTENT",(string)trim($post["post_content"])
								  ,"POST_AUTHOR",$pauth[0]
								  ,"POST_AUTHOR_URL",$pauth[1]
								  ,"TIME",date($this->config["time_struct"],$post["post_time"])
								  ,"POST_DATE",$date
								  ,"POST_URL",$this->ne_genpostlink($post["post_id"],true)
								  ,"COMMENT_COUNT",(string)$this->ne_postcomcount($post["post_id"],$id)
								  ));
		$content.=$tpl_post->tpl_return("posttpl");
		if($this->config["allow_com"]){

			//
	        //The start_post is equal to the current page number multiplied by the total number of posts per page plus one
	        //The end post is equal to the current page number plus one multiplied by the number of posts per page
	        //
	        $start_post=($GLOBALS['MANDRIGO']['VARS']['PAGE_NUMBER']*$this->config['com_num']);
	        $end_post=($GLOBALS['MANDRIGO']['VARS']['PAGE_NUMBER']+1)*$this->config['com_num']-1;
	        //total posts in the system
	        $total_posts=$this->ne_postcomcount($pid,$id);
	        //prevents system from exceding the max number of posts
	        if($end_post>$total_posts){
	            $end_post=$total_posts-1;
	        }
	
			$length=$end_post-$start_post+1;
			$com_str="";
	        //forms array of post id's from most current to oldest
	        if($total_posts!=0&&$total_posts){
		        $id_array=array_slice($this->ne_postid($id,$total_posts,TABLE_NEWS_COMMENTS,$pid),$start_post,$length);
				$soid=count($id_array);
		        $filter=array();
		        $filter[0]=array("post_id","=",$pid,DB_AND,1);
				for($i=0;$i<$soid;$i++){
					if($i+1<$soid){
						$filter[$i+1]=array("com_id","=",$id_array[$i]["com_id"],DB_OR,2);	
					}
					else{
						$filter[$i+1]=array("com_id","=",$id_array[$i]["com_id"],"",2);
					}
				}
				$filter[$soid+1]=array(DB_ORDERBY,"com_time","DESC","",3);
				$coms=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_NEWS_COMMENTS.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$id,"",$filter,"ASSOC",DB_ALL_ROWS);
				$soq=count($coms);
				for($i=0;$i<$soq;$i++){
					$tpl_p=new template();
					$tpl_p->tpl_load($this->tpl->tpl_return("com"),"comsing",false);
					$author=$this->ne_gencomauth($coms[$i]["com_author"]);
					$tpl_p->tpl_parse(array("COM_TITLE",$coms[$i]["com_title"]
										   ,"COM_CONTENT",$coms[$i]["com_content"]
										   ,"TIME",date($this->config["time_struct"],$coms[$i]["com_time"])
										   ,"DATE",date($this->config["date_struct"],$coms[$i]["com_time"])
										   ,"COM_AUTHOR_URL",$author[0]
										   ,"COM_AUTHOR",$author[1]));
					$com_str.=$tpl_p->tpl_return();
				}
				$this->tpl->tpl_parse(array("COMMENTS",$com_str,"NAV",$this->ne_navgen($total_posts,$pid)),"coms",1,false);
			}
			$content.=$this->tpl->tpl_return("coms");
			
			$tpl_addcom=new template();
			$tpl_addcom->tpl_load($this->tpl->tpl_return("addcom"),"addcommain",false);
			
			$action=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"id","p".$pid,"a","addcom"));
			$captcha="";
			if($this->config["use_captcha"]){
				$tpl_addcom->tpl_load($this->tpl->tpl_return("addcom"),"captcha",false);
				$ca=new captcha($id);
				$id_str=$ca->ca_genca();
				$ca=false;
				$img_path=$GLOBALS["MANDRIGO"]["SITE"]["IMG_URL"].TMP_IMG.$id_str.".jpg";
				$tpl_addcom->tpl_parse(array("CAPID",$id_str,"CA_IMG",$img_path),"captcha",1,false);
				$captcha=$tpl_addcom->tpl_return("captcha");
			}
						
			$ar=array("FORM_ACT",$action,"NAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["NAME"],"CAP",$captcha);
			if($GLOBALS["MANDRIGO"]["CURRENTUSER"]["AUTHENTICATED"]){
				$tpl_addcom->tpl_load($this->tpl->tpl_return("addcom"),"addcom_auth",false);
				$tpl_addcom->tpl_parse($ar,"addcom_auth",1,false);
				$tpl_addcom->tpl_parse(array("ERROR",$error,"ADDCOM",$tpl_addcom->tpl_return("addcom_auth")),"addcommain",1,false);
			}
			else if($this->config["allow_acom"]){
				$tpl_addcom->tpl_load($this->tpl->tpl_return("addcom"),"addcom_an",false);
				$tpl_addcom->tpl_parse($ar,"addcom_an",1,false);			
				$tpl_addcom->tpl_parse(array("ERROR",$error,"ADDCOM",$tpl_addcom->tpl_return("addcom_an")),"addcommain",1,false);
			}
			else{
				$tpl_addcom->tpl_load($this->tpl->tpl_return("addcom"),"addcomdenied",false);
				$tpl_addcom->tpl_parse($ar,"addcomdenied",1,false);				
				$tpl_addcom->tpl_parse(array("ERROR",$error,"ADDCOM",$tpl_addcom->tpl_return("addcomdenied")),"addcommain",1,false);
			}
			$content.=$tpl_addcom->tpl_return("addcommain");	
		}
		return $this->tpl->tpl_return("top").$content.$this->tpl->tpl_return("bot");
	}
	
	//
	//public function nd_addcomment($id);
	//
	//adds a user comment
	//
	function ne_addcomment($id){
	 
		if(eregi("p",$GLOBALS["MANDRIGO"]["VARS"]["ID"])){
			$pid=(int)ereg_replace("p","",$GLOBALS["MANDRIGO"]["VARS"]["ID"]);
		}
		else{
			$pid=(int)$GLOBALS["MANDRIGO"]["VARS"]["ID"];
		}
		
	  	if(!$this->config["allow_com"]){
			return $this->ne_displaypost($id,$GLOBALS["MANDRIGO"]["LANGUAGE"]["NEWS_COMOFF"]);
		}
		if($this->config["use_captcha"]){
			$ca=new captcha($id);
			if(!$ca->ca_checkca()){
				return $this->ne_displaypost($id,$GLOBALS["MANDRIGO"]["LANGUAGE"]["NEWS_BADCAP"]);
			}
		}
		if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_NEWS_COMVALUE"]){
			return $this->ne_displaypost($id,$GLOBALS["MANDRIGO"]["LANGUAGE"]["NEWS_NOCONTENT"]);	
		}
		if($GLOBALS["MANDRIGO"]["CURRENTUSER"]["AUTHENTICATED"]){
			
		}
		else if($this->config["allow_acom"]){
			$name=(empty($GLOBALS["MANDRIGO"]["VARS"]["MG_NEWS_COMNAME"]))?"Guest":trim($GLOBALS["MANDRIGO"]["VARS"]["MG_NEWS_COMNAME"]);
			$email=(empty($GLOBALS["MANDRIGO"]["VARS"]["MG_NEWS_COMEMAIL"]))?"Guest":trim($GLOBALS["MANDRIGO"]["VARS"]["MG_NEWS_COMEMAIL"]);
			$post=trim($GLOBALS["MANDRIGO"]["VARS"]["MG_NEWS_COMVALUE"]);
			$items=array("com_time","com_author","com_content","post_id");
			$values=array($GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"],"{$name}==>{$email}",$post,$pid);
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_NEWS_COMMENTS.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$id,$values,$items)){
				return $this->ne_displaypost($id,$GLOBALS["MANDRIGO"]["LANGUAGE"]["INTERNAL_ERROR"]);
			}
			return $this->ne_displaypost($id,$GLOBALS["MANDRIGO"]["LANGUAGE"]["NEWS_POSTED"]);
		}
		else{
			return $this->ne_displaypost($id,$GLOBALS["MANDRIGO"]["LANGUAGE"]["NOPERMISSION"]);
		}
		return $this->ne_displaypost($id);
	}	
	//
	//public function nd_displayfeed();
	//
	//displays an rss or atom feed
	//
	function ne_displayfeed($id){
	  	if(!$this->config['feed_allow']){
			return false;
		}
		if(ereg('rss',$GLOBALS['HTTP_GET']['FEED_TYPE'])){
			$GLOBALS['MANDRIGO']['LANGUAGE']['CONTENT_TYPE']=RSS_CONTENTTYPE;
			$GLOBALS['MANDRIGO']['LANGUAGE']['SET_ENCODING']=false;
		}
		else{
			$GLOBALS['MANDRIGO']['LANGUAGE']['CONTENT_TYPE']=ATOM_CONTENTTYPE;
			$GLOBALS['MANDRIGO']['LANGUAGE']['SET_ENCODING']=false;			
		}
		
		$feed_url=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"fd",$GLOBALS["MANDRIGO"]["VARS"]["FEED_TYPE"]));
		$page_url=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"]));
        $total_posts=$GLOBALS["MANDRIGO"]["DB"]->db_numrows(TABLE_PREFIX.TABLE_NEWS.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$id,'');
		$length=$total_posts;
        if($this->config["posts_num"]>$total_posts){
            $length=$this->config["posts_num"];
        }

		$length-=1;
		
        //forms array of post id's from most current to oldest
        $id_array=array_slice($this->ne_postid($id,$total_posts),0,$length);
		$soid=count($id_array);

        $filter=array();
        for($i=0;$i<$soid;$i++){
			if($i+1<$soid){
				$filter[$i]=array("post_id","=",$id_array[$i]["post_id"],DB_OR);	
			}
			else{
				$filter[$i]=array("post_id","=",$id_array[$i]["post_id"]);
			}
		}
		$filter[$soid]=array(DB_ORDERBY,"post_time","DESC");
		$posts=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_NEWS.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$id,"",$filter,"ASSOC",DB_ALL_ROWS);

		$soq=count($posts);
		$last_updated='';
		$posts_string='';
		$rss1_header='';
		for($i=0;$i<$soq;$i++){
			if($i==0){
				$last_updated=$this->ne_mkfeeddate($posts[$i]["post_id"]);	
			}
			$post_url=$this->ne_genpostlink($posts[$i]["post_id"]);
			$user=$this->ne_genuserlink($posts[$i]["post_author"]);
			if($GLOBALS["MANDRIGO"]["VARS"]["FEED_TYPE"]==FEED_RSS1){
				$cur_tpl=new template();
				$cur_tpl->tpl_load($this->tpl->tpl_return("feedoverview"),"feedoverviewsub",false);
				$cur_tpl->tpl_parse(array('POST_URL',$post_url));
				$rss1_header.=$cur_tpl->tpl_return("feedoverviewsub");
			}
			$cur_tpl=new template();
			$cur_tpl->tpl_load($this->tpl->tpl_return("feeditem"),"feeditemsub",false);
			$parse=array('POST_URL',$post_url
						,'POST_TITLE',$posts[$i]['post_title']
						,'POST_DATE',$this->ne_mkfeeddate($posts[$i]['post_time'])
						,'POST_USERNAME',$user[0]
						,'POST_USER_URL',$user[2]
						,'CONTENT',$posts[$i]['post_content']
						,'SITE_URL',$GLOBALS['MANDRIGO']['SITE']['SITE_URL']
						,'SITE_PAGE',$GLOBALS['MANDRIGO']["CURRENTPAGE"]['FNAME']
						,'POST_ID',$posts[$i]["post_id"]
						,'CONTENT_ENCODED',htmlspecialchars($posts[$i]['post_content'],ENT_QUOTES,$GLOBALS['LANGUAGE']['CHARSET'])
						,'CONTENT_NOHTML',strip_tags($posts[$i]['post_content']));
			$cur_tpl->tpl_parse($parse);
			$posts_string.=$cur_tpl->tpl_return();	
		}
		$feedparse=array('ENCODING',$GLOBALS["MANDRIGO"]['LANGUAGE']['ENCODING']
						,'MANDRIGO_VERSION',$GLOBALS['MANDRIGO']['SITE']['MANDRIGO_VER']
						,'FEED_LANG',$GLOBALS["MANDRIGO"]['LANGUAGE']['NAME']
						,'FEED_TITLE',$GLOBALS['MANDRIGO']['CURRENTPAGE']['FULLNAME'].' - '.$GLOBALS['MANDRIGO']['SITE']['SITE_NAME']
						,'FEED_DESCRIPTION',$GLOBALS['MANDRIGO']['CURRENTPAGE']['FULLNAME']
						,'FEED_URL',$feed_url
						,'PAGE_URL',$page_url
						,'UPDATE_PERIOD',$this->ne_convttl($this->config['feed_ttl'])
						,'TTL',$this->config['feed_ttl']
						,'UPDATE_FREQ',$this->config['feed_ud_freq']
						,'LAST_UPDATED',$last_updated
						,'POSTS',$posts_string
						,'FEED_OVERVIEW',$rss1_header);
		$this->tpl->tpl_parse($feedparse,"feed");
		echo $this->tpl->tpl_return("feed");
		die();
	}
	
	//
	//public function nd_returnvars();
	//
	//returns any page parse vars
	//
	function ne_returnvars(){
		return $this->pparse_vars;
	}
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################

	//
	//private ne_postid($pid,$total_posts,$table=TABLE_NEWS,$poid='');
	//
	//generates the bottom navigational bar
	//
	function ne_postid($pid,$total_posts,$table=TABLE_NEWS,$poid=''){
        $i=$total_posts;
        if($i==0){
			return false;
		}
		if(!$poid){
			$post_id_array=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.$table.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$pid,"post_id","","ASSOC",DB_ALL_ROWS);
		}
		else{
			$post_id_array=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.$table.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$pid,"com_id",array(array('post_id','=',$poid)),"ASSOC",DB_ALL_ROWS);			
		}
        return array_reverse($post_id_array);
    } 
	
	//
	//private function nd_navgen($total_length);
	//
	//generates the bottom navigational bar
	//
    function ne_navgen($total_length,$pid=false){
     	$max=($pid==false)?$this->config['posts_num']:$this->config['com_num'];
        if($total_length==0){
            $pages=1;
        }
        else{
            $pages=ceil(($total_length/($max)));
        }
        $pages-=1;
        if($pages>0){
            $c1='';
            for($i=0;$i<=$pages;$i++){     	
				$tpl_nav=new template();
             	$tpl_nav->tpl_load($this->tpl->tpl_return("nav0"),"linkn0",false);
             	$tpl_nav->tpl_parse(array("NAV_URL",$this->ne_navgenlink($i,$pid),"PAGE_NUM",(string)($i+1)));
				$c1.=$tpl_nav->tpl_return("linkn0");
				if(($i+1)<=$pages){
					$c1.=$this->config["nav0_delim"];
				}
            }
            if($GLOBALS["MANDRIGO"]["VARS"]["PAGE_NUMBER"]==0){
				$nav1=$this->tpl->tpl_return("nav1");
				$nav1=explode("<<|>>",$nav1);
				$tpl_nav=new template();
				$tpl_nav->tpl_load("<!--MG_TEMPLATE_START_main-->".$nav1[1]."<!--MG_TEMPLATE_END_main-->","main",false);
				$tpl_nav->tpl_parse(array("NEXT_URL",$this->ne_navgenlink(1,$pid)));
			}
			else if($GLOBALS["MANDRIGO"]["VARS"]["PAGE_NUMBER"]==$pages){
				$nav1=$this->tpl->tpl_return("nav1");
				$nav1=explode("<<|>>",$nav1);
				$tpl_nav=new template();
				$tpl_nav->tpl_load("<!--MG_TEMPLATE_START_main-->".$nav1[0]."<!--MG_TEMPLATE_END_main-->","main",false);
				$tpl_nav->tpl_parse(array("PREV_URL",$this->ne_navgenlink(0,$pid)));	
			}
			else{
				$nav1=$this->tpl->tpl_return("nav1");
				$nav1=explode("<<|>>",$nav1);
				$tpl_nav=new template();
				$tpl_nav->tpl_load("<!--MG_TEMPLATE_START_main-->".$nav1[0].$this->config["nav1_delim"].$nav1[1]."<!--MG_TEMPLATE_END_main-->","main",false);
				$tpl_nav->tpl_parse(array("PREV_URL",$this->ne_navgenlink($GLOBALS["MANDRIGO"]["VARS"]["PAGE_NUMBER"]-1,$pid)
								         ,"NEXT_URL",$this->ne_navgenlink($GLOBALS["MANDRIGO"]["VARS"]["PAGE_NUMBER"]+1,$pid)));
			}
        }
        else{
        	return "";
        }
		$this->tpl->tpl_parse(array("NAV0",$c1,"NAV1",$tpl_nav->tpl_return()),"nav",1,false);
        return $this->tpl->tpl_return("nav");
    }
    function ne_navgenlink($pn,$pid=false){
     	if($pid){
			return $this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"id","p".$pid,"pn",(string)$pn));	
		}
		return $this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"pn",(string)$pn));
	}
	//
	//private function nd_sameday($cur_time,$last_time);
	//
	//returns true if the timestamps are on the same day and false if they are not
	//
    function ne_sameday($cur_time,$last_time){
        if($last_time==0){
            return false;
        }
        if(date('d',$cur_time)===date('d',$last_time)){
            return true;
        }
        return false;
    }
    function ne_genrss(){
		//
		//generates the rss/atom links for further use
		//
		if($this->config['feed_allow']){
			$rss10_url=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"fd","rss1.0"));
			$rss92_url=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"fd","rss0.92"));
			$rss20_url=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"fd","rss2.0"));
		  	$atom_url=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"fd","atom"));
			$this->tpl->tpl_parse(array('RSS1.0_LINK',$rss10_url,'RSS0.92_LINK',$rss92_url,'RSS2.0_LINK',$rss20_url,'ATOM_LINK',$atom_url),"synd",1,false);
			$this->pparse_vars=array("SYNDICATION",$this->tpl->tpl_return("synd"));
		}
	}
	//
	//private function nd_genuser($uid);
	//
	//Loads necessary variables and templates
	//
    function ne_genuserlink($uid){
        $user=new account($uid);
        $uname=$user->ac_uname();
        if(!$uname){
			return array("N/A","");
		}
		$website=$user->ac_userdata();
		$website=$website["WEBSITE"];
        return array($uname,$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["SITE"]["PROFILE_PAGE"],"id","u".$uid)),$website);
    }
    
    function ne_genpostlink($pid,$com=false){
		if($com){
			$conds=array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"]);
		}
		else{
			$conds=array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"id","p".$pid);			
		}
		return $this->ne_genlink($conds);
	}
	
	function ne_postcomcount($pid,$id){
		$c=$GLOBALS["MANDRIGO"]["DB"]->db_numrows(TABLE_PREFIX.TABLE_NEWS_COMMENTS.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]."_".$id,array(array("post_id","=",$pid)));
		return (!empty($c))?$c:"0";
	}
	function ne_gencomauth($auth){
	 	if(eregi("==>",$auth)){
			$author=explode("==>",$auth);
			$name=(string)trim($author[0]);
			$email=(string)trim($author[1]);
			if($name==BAD_DATA||!$name){
				$name="N/A";
			}
			if($email==BAD_DATA||!$email){
				$email=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"id",$GLOBALS["MANDRIGO"]["VARS"]["ID"]));
			}
			else{
				$email=$this->ne_genlink(array("p",$GLOBALS["MANDRIGO"]["SITE"]["FORM_MAIL_PAGE"],"email",$email));
			}
		}
		else{
		
		}
		return array($email,$name);		
	}
    
	//
	//private function nd_genlink($url_data,$name,$type='internal');
	//
	//Loads necessary variables and templates
	//
    function ne_genlink($url_data,$name=''){
      	$link='';
 		if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
			$url=$GLOBALS['MANDRIGO']['SITE']['SITE_URL'].$GLOBALS['MANDRIGO']['SITE']['INDEX_NAME']."/";
		}
		else{
		  	$url=$GLOBALS['MANDRIGO']['SITE']['SITE_URL'].$GLOBALS['MANDRIGO']['SITE']['INDEX_NAME']."?";
		}  
		$soq=count($url_data);
		$i=0;
		while($i<$soq){
			if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
				$url.=$url_data[$i]."/".$url_data[$i+1];
				$i+=2;
				if($i<$soq){
					$url.="/";
				}
			}
			else{
				$url.=$url_data[$i]."=".$url_data[$i+1];
				$i+=2;
				if($i<$soq){
					$url.="&amp;";
				}
			}
		}
		return $url;
	}
	
	//
	//private function nd_convttl($ttl);
	//
	//converts the TTL into an update period
	//	
	function ne_convttl($ttl){
		return 'hourly';
	}
	
	function ne_mkfeeddate($timestamp){
		switch($GLOBALS['HTTP_GET']['FEED_TYPE']){
			case FEED_RSS092:
				return false;
			break;
			case FEED_RSS1:
				$diff_=date('O',$timestamp);
				$diff=substr($diff_,0,3).':'.substr($diff_,3,2);
				return date('Y-m-d',$timestamp).'T'.date('H:i:s',$timestamp).$diff;	
			break;
			case FEED_ATOM:
				$diff_=date('O',$timestamp);
				$diff=substr($diff_,0,3).':'.substr($diff_,3,2);
				return date('Y-m-d',$timestamp).'T'.date('H:i:s',$timestamp).$diff;	
			break;
			default:
				return date('D, d M Y H:i:s O',$timestamp);
			break;
		};		
	}
}
?>
