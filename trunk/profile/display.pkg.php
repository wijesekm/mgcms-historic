<?php
/**********************************************************
    display.pkg.php
    profile ver 0.6.0
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

class profile_display{
  
  	var $profile_db;

    function profile_display(&$db){
		$this->profile_db=$db;
    }
    function display_user($id){
		$tpl=new template();
		if(!$tpl->load($GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$id.TPL_PROFILE.'.'.TPL_EXT)){
			$GLOBALS['error_log']->add_error(4,'display');
			return false;
		}
		if($GLOBALS['HTTP_GET']['ID']==0){
            $GLOBALS['HTTP_GET']['ID']+=2;
        }
        if($GLOBALS['HTTP_GET']['ID']==1){
        	$GLOBALS['HTTP_GET']['ID']++;	
		}
		
		if(!$sql_result=$this->profile_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,'',array(array('user_id','=',$GLOBALS['HTTP_GET']['ID'])))){
			return $GLOBALS["HTML"]["P"].$GLOBALS['LANGUAGE']['PROF_NOUSER'].$GLOBALS["HTML"]["!P"];
		}
		$sql_result['user_group']=explode(';',$sql_result['user_group']);
		$group_name='';
		$group_admin=false;
		for($i=0;$i<count($sql_result['user_group']);$i++){
			if(!$sql_result1=$this->profile_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_GROUPS,'',array(array('group_id','=',$sql_result['user_group'][$i])))){
				$GLOBALS['error_log']->add_error(11,'sql');
				return false;
			}
			$group_name.=$this->gen_link_internal($GLOBALS['SITE_DATA']['PROFILE_PAGE'],$sql_result1['group_name'],'id','g'.$sql_result1['group_id']);
			if($i<count($sql_result['user_group'])-1){
				$group_name.=',';
			}
			if($sql_result1['group_admin']==1){
				$group_admin=true;
			}			
		}    
		$rn=explode(';',$sql_result['user_real_name']);
		$pparse_vars = array('USER_ID',$sql_result['user_id']
							,'USER_ANAME',$sql_result['user_name']
							,'USER_RNAME',$rn[0].' '.$rn[1].' '.$rn[2]
							,'USER_EMAIL',$this->gen_email($sql_result['user_id'],$rn[0],$sql_result['user_email'])
							,'USER_IM',$this->gen_im(explode(';',$sql_result['user_im']))
							,'USER_WEBSITE',$this->gen_link_external($sql_result['user_website'],$sql_result['user_website'])
							,'USER_ABOUT',$sql_result['user_about']
							,'USER_TITLE',$sql_result['user_title']
							,'USER_ICON',$this->gen_icon($sql_result['user_icon'])
							,'USER_IS_LOGGED_IN',($sql_result['user_session'])?$GLOBALS['LANGUAGE']['YES']:$GLOBALS['LANGUAGE']['NO']
							,'USER_LAST_TIME',$sql_result['user_last_login']
							,'USER_LAST_IP',$sql_result['user_last_ip']
							,'USER_GROUPS',$group_name
							,'USER_ADMIN',($group_admin)?$GLOBALS['LANGUAGE']['YES']:$GLOBALS['LANGUAGE']['NO']);
		$tpl->pparse($pparse_vars);
		return $tpl->return_template();
	
	}
	function display_group($id){
	  	$tpl=new template();
		if(!$tpl->load($GLOBALS['MANDRIGO_CONFIG']['TEMPLATE_PATH'].$GLOBALS['PAGE_DATA']['DATAPATH'].$GLOBALS['PAGE_DATA']['ID'].'_'.$id.TPL_GROUP_PROFILE.'.'.TPL_EXT)){
			$GLOBALS['error_log']->add_error(4,'display');
			return false;
		}
		if($GLOBALS['HTTP_GET']['ID']==0){
            $GLOBALS['HTTP_GET']['ID']+=2;
        }
        if($GLOBALS['HTTP_GET']['ID']==1){
        	$GLOBALS['HTTP_GET']['ID']++;	
		}
		if(!$sql_result=$this->profile_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_GROUPS,'',array(array('group_id','=',$GLOBALS['HTTP_GET']['ID'])))){
			return $GLOBALS["HTML"]["P"].$GLOBALS['LANGUAGE']['PROF_NOGROUP'].$GLOBALS["HTML"]["!P"];
		}
		$group_members='';
		$sql_result['group_users']=explode(';',$sql_result['group_users']);
		for($i=0;$i<count($sql_result['group_users']);$i++){
		  	if($sql_result1=$this->profile_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,'',array(array('user_id','=',$sql_result['group_users'][$i])))){
				$group_members.=$this->gen_link_internal($GLOBALS['SITE_DATA']['PROFILE_PAGE'],$sql_result1['user_name'],'id','u'.$sql_result['group_users'][$i]);
				if($i<count($sql_result['group_users'])-1){
					$group_members.=',';
				}	
			}
		}	
		$pparse_vars = array('GROUP_ID',$sql_result['group_id']
							,'GROUP_NAME',$sql_result['group_name']
							,'GROUP_ADMIN',($sql_result['group_admin'])?$GLOBALS['LANGUAGE']['YES']:$GLOBALS['LANGUAGE']['NO']
							,'GROUP_MEMBERS',$group_members
							,'GROUP_ABOUT',$sql_result['group_about']
							,'GROUP_ICON',$this->gen_icon($sql_result['group_icon']));
		$tpl->pparse($pparse_vars);
		return $tpl->return_template();	
	}
	function gen_email($id,$name,$email){
		if($eid=$this->profile_db->db_fetchresult(TABLE_PREFIX.TABLE_EMAIL_LIST,'email_id',array(array('user_id','=',$id)))&&$GLOBALS['SITE_DATA']['FORM_MAIL_PAGE']){
			return $this->gen_link_internal($GLOBALS['SITE_DATA']['FORM_MAIL_PAGE'],$GLOBALS['LANGUAGE']['EMAIL'].$name,'mail',$eid);
		}
		else{
			$link='href="#" onclick="document.location=\'mai\'+\'lto:';
			$email=explode('@',$email);
			$link.=$email[0].'\'+unescape(\'%40\')+\''.$email[1].'\'; return false;"';
			return ereg_replace('{ATTRIB}',$link,$GLOBALS['HTML']['A']).$name.$GLOBALS['HTML']['A!'];
		}
	}
	function gen_im($array){
	  	$string='';
	  	if(!count($array)||count($array)==1){
			return '';
		}
	  	for($i=0;$i<count($array);$i+=2){
			$string.=$array[$i].': ';
			if(eregi('AIM',$array[$i])){
				$string.=$this->gen_link_external($array[$i+1],'aim:goim?screenname='.$array[$i+1]);
			}
			else if(eregi('YIM',$array[$i])){
				$string.=$this->gen_link_external($array[$i+1],'http://edit.yahoo.com/config/send_webmesg?.target='.$array[$i+1].'&.src=pg');	
			}
			else if(eregi('ICQ',$array[$i])){
				$string.=$this->gen_link_external($array[$i+1],'http://wwp.icq.com/scripts/search.dll?to='.$array[$i+1]);					
			}
			else{
				$string.=$array[$i+1];
			}
			$string.=$GLOBALS['HTML']['BR'];
		}
		return $string;
	  
	}
	function gen_link_internal($page,$name,$form,$item){
        if($GLOBALS['SITE_DATA']['URL_FORMAT']==1){
            $link = $GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'].'/p/'.$page.'/'.$form.'/'.$item;
        }
        else{
            $link = $GLOBALS['SITE_DATA']['SITE_URL'].$GLOBALS['MANDRIGO_CONFIG']['INDEX'].'?p='.$page.'&amp;'.$form.'='.$item;
        }	
        return ereg_replace('{ATTRIB}','href="'.$link.'"',$GLOBALS['HTML']['A']).$name.$GLOBALS['HTML']['A!'];
	}
	function gen_link_external($name,$url){
		return ereg_replace('{ATTRIB}','href="'.$url.'"',$GLOBALS['HTML']['A']).$name.$GLOBALS['HTML']['A!'];
	}
	function gen_icon($path){
		if($path){
			return $this->gen_img($GLOBALS["SITE_DATA"]["IMG_URL"].ICONS_IMG."/".$path,$GLOBALS["LANGUAGE"]["ALT_ICON"]);
		}
		else{
			return $this->gen_img($GLOBALS["SITE_DATA"]["IMG_URL"].ICONS_IMG."/".BLANK_ICON,$GLOBALS["LANGUAGE"]["ALT_ICON"]);	
		}
	}
	function gen_img($src,$alt){
		return ereg_replace('{ATTRIB}','src="'.$src.'" alt="'.$alt.'"',$GLOBALS['HTML']['IMG']);
	}
}
?>
