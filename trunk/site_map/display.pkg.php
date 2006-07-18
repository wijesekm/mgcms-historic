<?php
/**********************************************************
    display.pkg.php
    site_map ver 0.6.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 7-17-06

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
if(!defined('START_MANDRIGO')){
    die('<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width="300" align="left"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>');
}

class site_map_display{

	var $sql_db;
	var $config;
	var $tpl;

    function site_map_display($sql){
        $this->sql_db=$sql;
    }
    function display($i){
	    if(!@$soq=$this->sql_db->db_numrows(TABLE_PREFIX.TABLE_PAGE_DATA,"")){
            return false;
        }
        $j=0;
        $data=ereg_replace('{ATTRIB}',$this->config['ul_attrib'],$GLOBALS['HTML']['UL']);
        for($i=1;$j<$soq;$i++){
			$current_page=$this->sql_db->db_fetcharray(TABLE_PREFIX.TABLE_PAGE_DATA,'',array(array('page_id','=',$i)));
			if($current_page){
				$j++;
				if($current_page['page_root']){
					if(eregi('==>',$current_page['page_name'])){
						$data.=$this->genlink_external($current_page['page_name'],$current_page['page_rname']);	
					}
					else{
					  	$page='';
						if($GLOBALS['SITE_DATA']['PAGE_INPUT_TYPE']){
							$page=$current_page['page_id'];
						}
						else{
							$page=$current_page['page_name'];
						}
						$data.=$this->genlink_internal($page,$current_page['page_rname']);	
					}
					$tmp_=$this->subpage($current_page,1,$current_page['page_id'].";");
					$data.=$tmp_;
				}
			}
		}
		$pparse_vars=array('SITE_MAP',$data.$GLOBALS['HTML']['UL!']);
		$this->tpl->pparse($pparse_vars);
		return $this->tpl->return_template();
	}
	function subpage($sql,$level,$prev){
	  	$first_ul=true;
	  	$end_ul=false;
		$data='';
		$sql['page_subpages']=explode(';',$sql['page_subpages']);
		$soq=count($sql['page_subpages']);
		for($i=0;$i<$soq;$i++){
		  	$current_page=$this->sql_db->db_fetcharray(TABLE_PREFIX.TABLE_PAGE_DATA,'',array(array('page_id','=',$sql['page_subpages'][$i])));
			if($current_page){
			  	if($first_ul){
					$data.=ereg_replace('{ATTRIB}',$this->config['li_attrib'].'style="list-style-type: none;"',$GLOBALS['HTML']['LI']).ereg_replace('{ATTRIB}',$this->config['ul_attrib'],$GLOBALS['HTML']['UL']);
					$first_ul=false;
					$end_ul=true;
				}
				if(eregi('==>',$current_page['page_name'])){
					$data.=$this->genlink_external($current_page['page_name'],$current_page['page_rname']);	
				}	
				else{
				  	$page='';
					if($GLOBALS['SITE_DATA']['PAGE_INPUT_TYPE']){
						$page=$current_page['page_id'];
					}
					else{
						$page=$current_page['page_name'];
					}
					$data.=$this->genlink_internal($page,$current_page['page_rname']);
				}
				if($this->check_infrec($current_page['page_id'],$prev)){
					$GLOBALS['error_log']->add_error(5,'display');
					return false;
				}
				$tmp_=$this->subpage($current_page,$level+1,$prev.';'.$current_page['page_id']);			
				$data.=$tmp_;
			}
		}
		if($end_ul){
			$data.=$GLOBALS['HTML']['UL!'].$GLOBALS['HTML']['LI!'];
		}
		return $data;
	}
	function check_infrec($id,$ids){
		$ids=explode(";",$ids);
		$soq=count($ids);
		for($i=0;$i<$soq;$i++){
			if($id==$ids[$i]){
				return true;
			}
		}
		return false;
	}
	function genlink_external($url,$name){
		$attr='href="'.ereg_replace('==>','',$url).'" '.$this->config['link_attrib'];
		$link=ereg_replace('{ATTRIB}',$attr,$GLOBALS['HTML']['A']).$name.$GLOBALS['HTML']['A!'];
		return ereg_replace('{ATTRIB}',$this->config['li_attrib'],$GLOBALS['HTML']['LI']).$link.$GLOBALS['HTML']['LI!'];
	}
	function genlink_internal($page,$name){
		if($GLOBALS['SITE_DATA']['URL_FORMAT']){
			$attr='href="'.$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'].'/p/'.$page.'" '.$this->config['link_attrib']; 
		}
		else{
	  		$attr='href="'.$GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'].'?p='.$page.'" '.$this->config['link_attrib']; 
		}
		$link=ereg_replace('{ATTRIB}',$attr,$GLOBALS['HTML']['A']).$name.$GLOBALS['HTML']['A!'];	
		return ereg_replace('{ATTRIB}',$this->config['li_attrib'],$GLOBALS['HTML']['LI']).$link.$GLOBALS['HTML']['LI!'];	
	}
    function load($i){
		if(!$sql_result=$this->sql_db->db_fetcharray(TABLE_PREFIX.TABLE_SITEMAP_DATA,'',array(array('page_id','=',$GLOBALS['PAGE_DATA']['ID'],DB_AND),array('part_id','=',$i)))){
            return false;
        }
        $this->config['link_attrib']=$sql_result['link_attrib'];
        $this->config['ul_attrib']=$sql_result['ul_attrib'];
        $this->config['li_attrib']=$sql_result['li_attrib'];
        $this->tpl=new template();
        if(!$this->tpl->load($GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$i.'_smp.'.TPL_EXT)){
			return false;
		}
        return true;
	}
}
?>
