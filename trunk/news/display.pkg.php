<?php
/**********************************************************
    display.pkg.php
    news ver 0.6.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 08/09/06

	Copyright (C) 2006 Kevin Wijesekera
	
	MandrigoCMS is Copyright (C) 2005-2006 the MandrigoCMS Group
	 
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
if(!defined('START_MANDRIGO')){
    die('<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width="300" align="left"/><p>You do not have permission to access this file directly.</p>
        </html></body>');
}


class news_display{


	//private vars
	var $news_db;
	var $tpl;
	var $config;
	var $pparse_vars;

	//Constructor
    function news_display(&$db){
		$this->news_db=$db;
    }
	
	//
	//public function nd_load($i,$type);
	//
	//Loads necessary variables and templates
	//
	function nd_load($id,$type){
	  	$this->tpl=new template();
		if(!$sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS_DATA,'',array(array('page_id','=',$GLOBALS['PAGE_DATA']['ID'],DB_AND),array('part_id','=',$id)))){
            $GLOBALS['error_log']->add_error(100,'sql');
			return false;
        }
        if($type==TPL_NEWS_SINGLE||$type==TPL_NEWS){
	        $this->config['num_per_page']=$sql_result['num_per_page'];
	        $this->config['date_struct']=$sql_result['date_struct'];
	        $this->config['time_struct']=$sql_result['time_struct'];
	        $this->config['allow_comments']=$sql_result['allow_comments'];
	        $this->config['allow_a_comments']=$sql_result['allow_a_comments'];
	        $this->config['show_rep_time']=$sql_result['show_rep_time'];
	        $this->config['com_per_page']=$sql_result['com_per_page'];
	        $this->config['feed_allow']=$sql_result['feed_allow'];
	        $this->config['use_captcha']=$sql_result['use_captcha'];
	        if($this->config['num_per_page']==0){
	        	$this->config['num_per_page']=1; 	
			}
			if($this->config['com_per_page']==0&&$this->config['allow_comments']){
				$this->config['com_per_page']=1;
			}
	        if(!$this->tpl->load($GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$id.'_'.$type.'.'.TPL_EXT,'','<!--NEWS_DELIM-->')){
				$GLOBALS['error_log']->add_error(6,'display');
				return false;
			}
		}
		else{
		  	$this->config['num_per_page']=$sql_result['num_per_page'];
		  	$this->config['allow_comments']=$sql_result['allow_comments'];
		  	$this->config['com_per_page']=$sql_result['com_per_page'];
		  	$this->config['feed_allow']=$sql_result['feed_allow'];
		  	$this->config['feed_ttl']=$sql_result['feed_ttl'];
		  	$this->config['feed_ud_freq']=$sql_result['feed_ud_freq'];
	        if(!$this->tpl->load($GLOBALS['MANDRIGO_CONFIG']['PLUGIN_PATH'].FEED_PATH.$type.'.'.TPL_EXT,'','<!--FEED_DELIM-->')){
				$GLOBALS['error_log']->add_error(6,'display');
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
    function nd_displayfull($id){
    	if(!$this->config){
			return false;
		}  
		//
		//generates the rss/atom links for further use
		//
		$feed_template=new template();
		if($this->config['feed_allow']){
			$rss10_url=$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'];
			$rss92_url=$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'];
			$rss20_url=$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'];
		  	$atom_url=$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'];
		  	if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
			    $atom_url.='/p/'.$GLOBALS['HTTP_GET']['PAGE'].'/fd/1/fdt/atom';
			    $rss92_url.='/p/'.$GLOBALS['HTTP_GET']['PAGE'].'/fd/1/fdt/rss0.92';
			    $rss10_url.='/p/'.$GLOBALS['HTTP_GET']['PAGE'].'/fd/1/fdt/rss1.0';
				$rss20_url.='/p/'.$GLOBALS['HTTP_GET']['PAGE'].'/fd/1/fdt/rss2.0';
			}
			else{
			    $atom_url.='?p='.$GLOBALS['HTTP_GET']['PAGE'].'&amp;fd=1&amp;fdt=atom';
			    $rss92_url.='?p='.$GLOBALS['HTTP_GET']['PAGE'].'&amp;fd=1&amp;fdt=rss0.92';
			    $rss10_url.='?p='.$GLOBALS['HTTP_GET']['PAGE'].'&amp;fd=1&amp;fdt=rss1.0';
				$rss20_url.='?p='.$GLOBALS['HTTP_GET']['PAGE'].'&amp;fd=1&amp;fdt=rss2.0';
			}
			$feed_template=new template();
			$feed_template->load('',$this->tpl->return_template(5));
			$feed_template->pparse(array('RSS1.0_LINK',$rss10_url,'RSS0.92_LINK',$rss92_url,'RSS2.0_LINK',$rss20_url,'ATOM_LINK',$atom_url));
		}
		
		//
        //The start_post is equal to the current page number multiplied by the total number of posts per page plus one
        //The end post is equal to the current page number plus one multiplied by the number of posts per page
        //
        $start_post=($GLOBALS['HTTP_GET']['PAGE_NUMBER']*$this->config['num_per_page'])+1;
        $end_post=($GLOBALS['HTTP_GET']['PAGE_NUMBER']+1)*$this->config['num_per_page'];
        //total posts in the system
        $total_posts=$this->news_db->db_numrows(TABLE_PREFIX.TABLE_NEWS.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$id,'');
        if($total_posts==0){
			return $GLOBALS["LANGUAGE"]["NEWS_NO_POSTS"];
		}
        //prevents system from exceding the max number of posts
        if($end_post>$total_posts){
            $end_post=$total_posts;
        }
		//$start_post--;     
        //forms array of post id's from most current to oldest
        $id_array=$this->nd_postid($id,$total_posts);
        $last_time=0;
        $dd_date=true;
        $post_content='';
        //generates the content string
        if($total_posts!=0){
	        while($start_post<=$end_post){
	            if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$id,'',array(array('post_id','=',$id_array[$start_post])))){
	                $dd_date=true;
					if($this->nd_sameday($sql_result['post_time'],$last_time)&&$this->config['show_rep_time']==0){
	                    $dd_date=false;
	                }
	                $parse_vars = array(
	                         'NEWS_MAIN_TITLE',$sql_result['post_title']
	                        ,'NEWS_MAIN_DATE',date($this->config['date_struct'],$sql_result['post_time'])
	                        ,'NEWS_MAIN_TIME',$this->nd_genlink(array('p',$GLOBALS['HTTP_GET']['PAGE'],'id',$id_array[$start_post]),date($this->config['time_struct'],$sql_result['post_time']))
	                        ,'NEWS_MAIN_USER',$this->nd_genuser($sql_result['post_author'])
	                        ,'NEWS_MAIN_CONTENT',$sql_result['post_content']);
	                $last_time = $sql_result['post_time'];
	                $cur_tpl= new template();
	            	$cur_tpl->load(false,$this->tpl->return_template(1).'<!--NEWS_DELIM-->'.$this->tpl->return_template(2),'<!--NEWS_DELIM-->');
	            	$cur_tpl->pparse($parse_vars,true,true,array($dd_date,true));
	            	if($dd_date){
	            		$post_content.=$cur_tpl->return_template(0); 
	            	}
	            	$post_content.=$cur_tpl->return_template(1);
	            }
	            $start_post++;
	        }
			$nav=$this->nd_navgen($total_posts);
			$tpl=$this->tpl->return_template(0).$this->tpl->return_template(3).$this->tpl->return_template(4);
			if(!$nav){
				$tpl=$this->tpl->return_template(0).$this->tpl->return_template(3);
			} 
			$this->pparse_vars=array('NEWS_POSTS',$post_content
									,'NEWS_PAGE_LIST',$nav[0]
									,'NEWS_NAV',$nav[1]
									,'RSS_ATOM',$feed_template->return_template()); 
		}
		else{
		  	$tpl=$this->tpl->return_template(0).$this->tpl->return_template(3);
			$this->pparse_vars=array('NEWS_POSTS',$GLOBALS['LANGUAGE']['NO_POSTS']
									,'NEWS_PAGE_LIST',''
									,'NEWS_NAV',''
									,'RSS_ATOM',$feed_template->return_template()); 			
		}
		return $tpl;
	}
	
	
	//
	//public function nd_displaypost($id,$etype=0,$error='');
	//
	//Displays an individual post
	//
	function nd_displaypost($id,$etype=0,$error=''){
    	if(!$this->config){
			return false;
		} 
		
		$template=$this->tpl->return_template();
		$post_content='';
		$post_add='';
		$act='';
		
		if($this->config['allow_comments']){
	        $start_post = ($GLOBALS['HTTP_GET']['PAGE_NUMBER']*$this->config['com_per_page'])+1;
	        $end_post = ($GLOBALS['HTTP_GET']['PAGE_NUMBER']+1)*$this->config['com_per_page'];
	    	$total_posts=$this->news_db->db_numrows(TABLE_PREFIX.TABLE_NEWS_COMMENTS.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$id,array(array('post_id','=',$GLOBALS['HTTP_GET']['ID'])));
	        if($end_post > $total_posts){
	            $end_post = $total_posts;
	        }
	        if($end_post==0){
				$start_post=0;
			}

	        $id_array = $this->nd_postid($id,$total_posts,TABLE_NEWS_COMMENTS,$GLOBALS['HTTP_GET']['ID']);
	        while($start_post <= $end_post){
	            if($sql_result1=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS_COMMENTS.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$id,'',array(array('post_id','=',$GLOBALS['HTTP_GET']['ID'],DB_AND),array('com_id','=',$id_array[$start_post])))){
					if($sql_result1['user_id']){
						$user=$this->gen_user($sql_result1['user_id']);	
					}
					else{
						$user=$this->nd_genlink(array('p',$GLOBALS['SITE_DATA']['FORM_MAIL_PAGE'],'mail',$sql_result1['user_email']),$sql_result1['user_name']);	
					}
					
					$parse_vars = array(
	                		 'NEWS_COM_DATE',date($this->config['date_struct'],$sql_result1['com_time'])
	                        ,'NEWS_COM_TIME',date($this->config['time_struct'],$sql_result1['com_time'])
	                        ,'NEWS_COM_COMMENT',$sql_result1['comment']
							,'NEWS_COM_USER',$user);
	                $cur_tpl= new template();
	            	$cur_tpl->load(false,$this->tpl->return_template(2));
	            	$cur_tpl->pparse($parse_vars);
	            	$post_content.=$cur_tpl->return_template();
	            }
	            $start_post++;
	        }
			$template.=$this->tpl->return_template(1).$this->tpl->return_template(3);
			if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
            	$act = $GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'].'/p/'.$GLOBALS['HTTP_GET']['PAGE'].'/id/'.$GLOBALS['HTTP_GET']['ID'].'/a/p';
        	}
        	else{
            	$act = $GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'].'?p='.$GLOBALS['HTTP_GET']['PAGE'].'&amp;id='.$GLOBALS['HTTP_GET']['ID'].'&amp;a=p';
        	}
			if($this->config['allow_a_comments']&&!$GLOBALS['USER_DATA']['AUTHENTICATED']){
				$post_add.=$this->tpl->return_template(5);	
			}				
			else if(!$this->config['allow_a_comments']&&!$GLOBALS['USER_DATA']['AUTHENTICATED']){
				$post_add.=$this->tpl->return_template(4);	
			}
			else if(!$this->config['allow_a_comments']&&$GLOBALS['USER_DATA']['AUTHENTICATED']){
				$post_add.=$this->tpl->return_template(6);	
			}
		}
		//gets the post from the database
		if(!$sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$id,'',array(array('post_id','=',$GLOBALS['HTTP_GET']['ID'])))){
			return $GLOBALS['LANGUAGE']['NO_POST'];  
		}
		$e2='';
		if(!$post_content){
		  	$e2=$GLOBALS['LANGUAGE']['NEWS_NO_COM'];			
		}
		$log='';
		if($error){
			if($etype==0){
				$log.=ereg_replace('{ATTRIB}',' style=\'color: #FF0000;\'',$GLOBALS['HTML']['P']);
			}
			else if($etype==1){
				$log.=ereg_replace('{ATTRIB}',' style=\'color: #00FF00;\'',$GLOBALS['HTML']['P']);
			}
			else{
				$log.=ereg_replace('{ATTRIB}','',$GLOBALS['HTML']['P']);	
			}
			$soq=count($error);
			for($i=0;$i<$soq;$i++){
				$log.=$error[$i].$GLOBALS['HTML']['BR'];
			}
			$log.=$GLOBALS['HTML']['!P'];
			if($e2){
				$log.=ereg_replace('{ATTRIB}','',$GLOBALS['HTML']['P']).$e2.$GLOBALS['HTML']['!P'];
			}
		}
		$captcha='';
		if($this->config['use_captcha']&&!$GLOBALS['USER_DATA']['AUTHENTICATED']){
			$ca=new captcha($this->news_db,$id);
			$cid=$ca->ca_genca();
			unset($ca);
			$ca_tpl=new template();
			$ca_tpl->load('',$this->tpl->return_template(7));
			$ca_tpl->pparse(array('CA_IMG_PATH',$GLOBALS["SITE_DATA"]["IMG_URL"].TMP_IMG.$cid.'.jpg','CA_ID',$cid));
			$captcha=$ca_tpl->return_template();
		}
		$this->pparse_vars=array('NEWS_MAIN_DATE',date($this->config['date_struct'],$sql_result['post_time'])
								,'NEWS_MAIN_TITLE',$sql_result['post_title']
								,'NEWS_MAIN_TIME',date($this->config['time_struct'],$sql_result['post_time'])
								,'NEWS_MAIN_USER',$this->nd_genuser($sql_result['post_author'])
								,'NEWS_MAIN_CONTENT',$sql_result['post_content']
								,'NEWS_COM',$post_content
								,'NEWS_COM_ERROR',$log
								,'NEWS_COM_ADD_COMMENT',$post_add
								,'NEWS_COM_ACTION',$act
								,'NEWS_CAPTCHA',$captcha);
		return $template;			
	}
	
	//
	//public function nd_addcomment($id);
	//
	//adds a user comment
	//
	function nd_addcomment($id){
	  	$halt=false;
	  	$errors=array();
		if($this->config['allow_comments']){
			if($GLOBALS['USER_DATA']['AUTHENTICATED']){
			  	if(!$GLOBALS['HTTP_POST']['NEWS_COMMENT']||$GLOBALS['HTTP_POST']['NEWS_COMMENT']===BAD_DATA){
					$halt=true;
					$errors=array_merge($errors,array($GLOBALS['LANGUAGE']['NEWS_BAD_POST']));
				}
			  	$items=array($GLOBALS['HTTP_GET']['ID']
							,time()
							,$GLOBALS['USER_DATA']['ID']
							,$GLOBALS['USER_DATA']['NAME']
							,$GLOBALS['USER_DATA']['EMAIL']
							,$GLOBALS['HTTP_POST']['NEWS_COMMENT']);
			}
			else if($this->config['allow_a_comments']&&!$GLOBALS['USER_DATA']['AUTHENTICATED']){
			  	if(!$GLOBALS['HTTP_POST']['NEWS_COM_NAME']||$GLOBALS['HTTP_POST']['NEWS_COM_NAME']===BAD_DATA){
					$halt=true;
					$errors=array_merge($errors,array($GLOBALS['LANGUAGE']['NEWS_BAD_NAME']));
				}
			  	if(!$GLOBALS['HTTP_POST']['NEWS_COM_EMAIL']||$GLOBALS['HTTP_POST']['NEWS_COM_EMAIL']===BAD_DATA){
					$halt=true;
					$errors=array_merge($errors,array($GLOBALS['LANGUAGE']['NEWS_BAD_EMAIL']));
				}
			  	if(!$GLOBALS['HTTP_POST']['NEWS_COMMENT']||$GLOBALS['HTTP_POST']['NEWS_COMMENT']===BAD_DATA){
					$halt=true;
					$errors=array_merge($errors,array($GLOBALS['LANGUAGE']['NEWS_BAD_POST']));
				}
				if($this->config['use_captcha']){
					$ca=new captcha($this->news_db,$id);
					if(!$ca->ca_checkca()){
						$halt=true;
						$errors=array_merge($errors,array($GLOBALS['LANGUAGE']['NEWS_INVALID_CAPTCHA']));
					}	
				}
			  	$items=array($GLOBALS['HTTP_GET']['ID']
							,time()
							,0
							,$GLOBALS['HTTP_POST']['NEWS_COM_NAME']
							,$GLOBALS['HTTP_POST']['NEWS_COM_EMAIL']
							,$GLOBALS['HTTP_POST']['NEWS_COMMENT']);			
			}
			if(!$halt){
				if(!$this->news_db->db_update(DB_INSERT,TABLE_PREFIX.TABLE_NEWS_COMMENTS.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$id,$items,array('post_id','com_time','user_id','user_name','user_email','comment'))){
					$GLOBALS['error_log']->add_error(101,'sql');
					return false;	
				}
				return $this->nd_displaypost($id,1,array($GLOBALS['LANGUAGE']['NEWS_COM_SUCCESS']));
			}
			else{
				return $this->nd_displaypost($id,0,$errors);
			}
		}
	}	
	//
	//public function nd_displayfeed();
	//
	//displays an rss or atom feed
	//
	function nd_displayfeed($id){
	  	if(!$this->config['feed_allow']){
			return false;
		}
		if(ereg('rss',$GLOBALS['HTTP_GET']['FEED_TYPE'])){
			$GLOBALS['LANGUAGE']['CONTENT_TYPE']=RSS_CONTENTTYPE;
			$GLOBALS['LANGUAGE']['SET_ENCODING']=false;
		}
		else{
			$GLOBALS['LANGUAGE']['CONTENT_TYPE']=ATOM_CONTENTTYPE;
			$GLOBALS['LANGUAGE']['SET_ENCODING']=false;			
		}
		
	  	$feed_url=$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'];
	  	if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
		    $feed_url.='/p/'.$GLOBALS['HTTP_GET']['PAGE'];
		}
		else{
		    $feed_url.='?p='.$GLOBALS['HTTP_GET']['PAGE'];
		}
	  	$atom_url=$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'];
	  	if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
		    $atom_url.='/p/'.$GLOBALS['HTTP_GET']['PAGE'].'/fd/1/fdt/atom';
		}
		else{
		    $atom_url.='?p='.$GLOBALS['HTTP_GET']['PAGE'].'&amp;fd=1&amp;fdt=atom';
		}
		$total_posts=$this->news_db->db_numrows(TABLE_PREFIX.TABLE_NEWS.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$id,'');
		$id_array=$this->nd_postid($id,$total_posts);
		if($total_posts<$this->config['num_per_page']){
			$soq=$total_posts;
		}
		else{
			$soq=$this->config['num_per_page'];
		}
		$last_updated='';
		$posts='';
		$seq='';
		for($i=1;$i<=$soq;$i++){
	    	if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_NEWS.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$id,'',array(array('post_id','=',$id_array[$i])))){
				if(!$sql_result2=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,'',array(array('user_id','=',$sql_result['post_author'])))){
					return false;
				}
				if($i==1){
					$last_updated=$this->nd_mkfeeddate($sql_result['post_time']);	
				}
				$cur_tpl=new template();
				$cur_tpl->load('',$this->tpl->return_template(1));

				$post_url='';
	  			if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
		    		$post_url=$feed_url.'/id/'.$sql_result['post_id'];
				}
				else{
		    		$post_url=$feed_url.'&amp;id='.$sql_result['post_id'];
				}
				if($this->tpl->return_template(2)){
					$seq_tpl=new template();
					$seq_tpl->load('',$this->tpl->return_template(2));
					$seq_tpl->pparse(array('POST_URL',$post_url));
					$seq.=$seq_tpl->return_template();
				}				
				$parse=array('POST_URL',$post_url
							,'POST_TITLE',$sql_result['post_title']
							,'POST_DATE',$this->nd_mkfeeddate($sql_result['post_time'])
							,'POST_USERNAME',$sql_result2['user_name']
							,'POST_USER_URL',$sql_result2['user_website']
							,'CONTENT',$sql_result['post_content']
							,'SITE_URL',$GLOBALS['SITE_DATA']['SITE_URL']
							,'SITE_PAGE',$GLOBALS['PAGE_DATA']['RNAME']
							,'POST_ID',$sql_result['post_id']
							,'CONTENT_ENCODED',htmlspecialchars($sql_result['post_content'],ENT_QUOTES,$GLOBALS['LANGUAGE']['CHARSET'])
							,'CONTENT_NOHTML',strip_tags($sql_result['post_content']));
				$cur_tpl->pparse($parse);
				$posts.=$cur_tpl->return_template();
	    	}	
		}	
		$feedparse=array('ENCODING',$GLOBALS['LANGUAGE']['ENCODING']
						,'MANDRIGO_VERSION',$GLOBALS['SITE_DATA']['MANDRIGO_VER']
						,'FEED_LANG',$GLOBALS['LANGUAGE']['NAME']
						,'FEED_TITLE',$GLOBALS['PAGE_DATA']['RNAME'].' - '.$GLOBALS['SITE_DATA']['SITE_NAME']
						,'FEED_DESCRIPTION',$GLOBALS['PAGE_DATA']['RNAME']
						,'FEED_URL',$feed_url
						,'UPDATE_PERIOD',$this->nd_convttl($this->config['feed_ttl'])
						,'TTL',$this->config['feed_ttl']
						,'UPDATE_FREQ',$this->config['feed_ud_freq']
						,'ATOM_URL',$atom_url
						,'LAST_UPDATED',$last_updated
						,'POSTS',$posts
						,'FEED_OVERVIEW',$seq);
		$this->tpl->pparse($feedparse);
		echo $this->tpl->return_template();
		die();
	}
	
	//
	//public function nd_returnvars();
	//
	//returns any page parse vars
	//
	function nd_returnvars(){
		return $this->pparse_vars;
	}
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################

	//
	//private function nd_navgen($total_length);
	//
	//generates the bottom navigational bar
	//
	function nd_postid($pid,$total_posts,$table=TABLE_NEWS,$poid=''){
        $i=$total_posts;
        if($i==0){
			return false;
		}
        $j=0;
        $post_id_array=array();
        while($i>0){
          	if(!$poid){
	            if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.$table.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$pid,'',array(array('post_id','=',$j)))){
					$post_id_array[$i] = $j;
	                $i--;
	            }
	        }
	        else{     	
	        	if($sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.$table.'_'.$GLOBALS['PAGE_DATA']['ID'].'_'.$pid,'',array(array('post_id','=',$poid,DB_AND),array('com_id','=',$j)))){
					$post_id_array[$i]=$j;
	                $i--;
	            }			
			}
            $j++;
        }
        return $post_id_array;
    } 
	
	//
	//private function nd_navgen($total_length);
	//
	//generates the bottom navigational bar
	//
    function nd_navgen($total_length){
        $nav[0]='';
        $nav[1]='';
        if($total_length==0){
            $pages=0;
        }
        else{
            $pages=ceil(($total_length/($this->config['num_per_page'])));
        }
        $pages-=1;
        if($pages>0){
            $i=0;
            while($i<=$pages){
              	$nav[0].=$this->nd_genlink(array('p',$GLOBALS['HTTP_GET']['PAGE'],'n',$i),($i+1));
     		    $i++;
     		    if($i<=$pages&&$i%20){
					$nav[0].=']'.$GLOBALS['HTML']['BR'].'[';
				}
                else if($i<=$pages){
                    $nav[0].=',';
                }
            }
            if($GLOBALS['HTTP_GET']['PAGE_NUMBER']==0){
              	$nav[1].=$this->nd_genlink(array('p',$GLOBALS['HTTP_GET']['PAGE'],'n',($GLOBALS['HTTP_GET']['PAGE_NUMBER']+1)),'Next');
            }
            else if($GLOBALS['HTTP_GET']['PAGE_NUMBER']==$pages){
              	$nav[1].=$this->nd_genlink(array('p',$GLOBALS['HTTP_GET']['PAGE'],'n',($GLOBALS['HTTP_GET']['PAGE_NUMBER']-1)),'Prev');
            }
            else{
              	$nav[1].=$this->nd_genlink(array('p',$GLOBALS['HTTP_GET']['PAGE'],'n',($GLOBALS['HTTP_GET']['PAGE_NUMBER']+1)),'Next');
              	$nav[1].=$this->nd_genlink(array('p',$GLOBALS['HTTP_GET']['PAGE'],'n',($GLOBALS['HTTP_GET']['PAGE_NUMBER']-1)),'Prev');
            }
        }
        else{
        	return false;
        }
        return $nav;
    }
    
	//
	//private function nd_sameday($cur_time,$last_time);
	//
	//returns true if the timestamps are on the same day and false if they are not
	//
    function nd_sameday($cur_time,$last_time){
        if($last_time==0){
            return false;
        }
        if(date('d',$cur_time)===date('d',$last_time)){
            return true;
        }
        return false;
    }
    
	//
	//private function nd_genuser($uid);
	//
	//Loads necessary variables and templates
	//
    function nd_genuser($uid){
        if(!@$sql_result=$this->news_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,'',array(array('user_id','=',$uid)))){
        	$GLOBALS['error_log']->add_error(10,'sql');
            return false;
        }
        return $this->nd_genlink(array('p',$GLOBALS['SITE_DATA']['PROFILE_PAGE'],'id','u'.$uid),$sql_result['user_name']);
    }
    
	//
	//private function nd_genlink($str);
	//
	//Replaces all \n's with <br/>'s for input data
	//
   	function nd_pad($str){
        return ereg_replace('\n','<br/>',$str);
    }
    
	//
	//private function nd_genlink($url_data,$name,$type='internal');
	//
	//Loads necessary variables and templates
	//
    function nd_genlink($url_data,$name,$type='internal'){
      	$link='';
    	if($type=='internal'){
    	  	$soq=count($url_data);
    	  	$link.=$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'];
    	  	for($i=0;$i<$soq;$i+=2){
    	  	  	
    	  	  	if($i==0){
					if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
						$link.='/';
					}
					else{
						$link.='?';
					}
				}
		        if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
		            $link.=$url_data[$i].'/'.$url_data[$i+1];
		        }
		        else{
		          	$link.=$url_data[$i].'='.$url_data[$i+1];
		        }
				if($i+1<$soq){
					if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
						$link.='/';
					}
					else{
						$link.='&amp;';
					}					
				}	
		    }
	    }
	    else if($type=='external'){
			$link=$url_data;	
		}
	    return ereg_replace('{ATTRIB}','href="'.$link.'"',$GLOBALS['HTML']['A']).$name.$GLOBALS['HTML']['A!'];
	}
	
	//
	//private function nd_convttl($ttl);
	//
	//converts the TTL into an update period
	//	
	function nd_convttl($ttl){
		return 'hourly';
	}
	
	function nd_mkfeeddate($timestamp){
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
