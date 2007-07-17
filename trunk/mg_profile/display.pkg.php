<?php
/**********************************************************
    display.pkg.php
    mg_profile ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 05/16/07

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

class mg_profile{

	var $tpl;

    function mg_profile($id){
		$this->tpl=new template();
        $file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['NAME'].'_'.$i.'.'.TPL_EXT;
		if(!$this->tpl->tpl_load($file,"overview")||!$this->tpl->tpl_load($file,"user")||!$this->tpl->tpl_load($file,"group")){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror('display',150);
			return false;
		}		
    }
    function pr_display($type){
     	$str='';
		switch($type){
			case 'group':
				$group=new group()
				//display group
			break;
			case 'user':
				if($GLOBALS["MANDRIGO"]["VARS"]["ID"] < 1){
					$GLOBALS["MANDRIGO"]["VARS"]["ID"]=1;
				}
				$cuser_acct=new account($GLOBALS["MANDRIGO"]["VARS"]["ID"]);
				$user_data=ac_userdata();
				if(!$user_data){
					return false;
				}
				$parse=array(
					"FIRST_NAME",$user_data["FNAME"],
					"MIDDLE_NAME",$user_data["MNAME"],
					"LAST_NAME",$user_data["LNAME"],
					"FULL_NAME",$user_data["FNAME"].$user_data["MNAME"].$user_data["LNAME"],
					"FULL_NAME_NO_MIDDLE",$user_data["FNAME"].$user_data["LNAME"],
					"EMAIL",$this->pr_genemail($user_data["EMAIL"],$user_data["UID"],$user_data["FNAME"].$user_data["LNAME"]),
					"IM",$this->pr_genim($user_data["IM"]),
					"WEBSITE",$this->pr_genlinkext($user_data["WEBSITE"],$user_data["WEBSITE"])
					"ABOUT",$user_data"ABOUT"],
					"PICTURE_PATH",$this->pr_genpicurl($user_data["PICTURE_PATH"]),
					"USER_ID",$user_data["UID"],
					"USER_NAME",$user_data["USERNAME"]
					"GROUPS",$this->pr_genusergroups($cuser_acct->ac_groups()));
					
					$this->tpl->tpl_parse($parse,"user");
					$str=$this->tpl->tpl_return("user");				
			break;
			default:
				$parse=array(
					"FIRST_NAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["FNAME"],
					"MIDDLE_NAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["MNAME"],
					"LAST_NAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LNAME"],
					"FULL_NAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["FNAME"].$GLOBALS["MANDRIGO"]["CURRENTUSER"]["MNAME"].$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LNAME"],
					"FULL_NAME_NO_MIDDLE",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["FNAME"].$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LNAME"],
					"EMAIL",$this->pr_genemail($GLOBALS["MANDRIGO"]["CURRENTUSER"]["EMAIL"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["FNAME"].$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LNAME"]),
					"IM",$this->pr_genim($GLOBALS["MANDRIGO"]["CURRENTUSER"]["IM"]),
					"WEBSITE",$this->pr_genlinkext($GLOBALS["MANDRIGO"]["CURRENTUSER"]["WEBSITE"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["WEBSITE"])
					"ABOUT",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ABOUT"],
					"PICTURE_PATH",$this->pr_genpicurl($GLOBALS["MANDRIGO"]["CURRENTUSER"]["PICTURE_PATH"]),
					"USER_ID",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"],
					"USER_NAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["USERNAME"]
					"GROUPS",$this->pr_genusergroups($GLOBALS["MANDRIGO"]["CURRENTUSER"]["GROUPS"]));
					
					$this->tpl->tpl_parse($parse,"user");
					$str=$this->tpl->tpl_return("user");
			break;	
		};
		$this->tpl->tpl_parse(array("PROFILE",$str),"overview");
		return $this->tpl_return("overview");
	}
	function pr_genusergroups($groups){
		$soq=count($groups);
		$str='';
		$li=ereg_replace("{ATTRIB}","",$GLOBALS["MANDRIGO"]["HTML"]["LI"]);
		for($i=0;$i<$soq;$i++){
		 	$url=$this->pr_genurl(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"id","g".$groups[$i]));
			$str.=ereg_replace("{ITEM}",,$li);
		}
		return $str;
	}
	function pr_genemail($email="",$userid=0,$name=""){
		$email_db=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_FMAIL_LIST,"email_id",array(array("uid","=",$userid)));
		if($email_db["email_id"]>1){
			$attribs=$this->pr_genurl(array("p",$GLOBALS["MANDRIGO"]["SITE"]["FORM_MAIL_PAGE"],"email",$email_db["email_id"]));
			$attribs='href="'.$attribs.'"';
			return $this->pr_genlinkext($attribs,$name);
		}
		else if($email!=""){
		 	$email=explode("@",$email);
		 	$url="document.location='mai'+'lto:".$email[0]."'+unescape('%40')+'".$email[1]."'; return false;";
			$attribs='href="#" onclick="'.$url.'"';
			return $this->pr_genlinkext($attribs,$name);
		}
		return false;
	}
	function pr_genpicurl($path){
		$url=$GLOBALS["MANDRIGO"]["CONFIG"]["IMG_URL"].USER_IMG_PATH;
		if(is_file($url.$path)){
			return $url.$path;
		}
		else{
			return $url.BLANK_ICON;
		}
	}
	function pr_genim($im){
		$soq=count($im);
		$li=ereg_replace("{ATTRIB}","",$GLOBALS["MANDRIGO"]["HTML"]["LI"]);
		$str='';
		for($i=0;$i<$soq;$i+=2){
		 	
			switch(mb_strtoupper($im[$i])){
				case "AIM":
				case "AOL":
					$str.=ereg_replace("{ITEM}","AIM".": ".$this->pr_genlinkext("aim:goim?screenname=".$im[$i+1],$im[$i+1]),$li);	
				break;
				case "ICQ":
					$str.=ereg_replace("{ITEM}","ICQ".": ".$this->pr_genlinkext("http://wwp.icq.com/scripts/search.dll?to=".$im[$i+1],$im[$i+1]),$li);					
				break;
				case "YAHOO":
				case "YIM":
					$str.=ereg_replace("{ITEM}","YAHOO".": ".$this->pr_genlinkext("http://edit.yahoo.com/config/send_webmesg?.target=".$im[$i+1].'&amp;.src=pg',$im[$i+1]),$li);				
				break;
				default:	
					$str.=ereg_replace("{ITEM}",$im[$i].": ".$im[$i+1],$li);
				break;
			}
		}
		return $str;
	}
    function pr_genurl($url_data){
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
	function pr_genlinkext($url,$name){
		$link=ereg_replace("{ATTRIB}",$url,$GLOBALS["MANDRIGO"]["HTML"]["A"]);
		return ereg_replace("{NAME}",$name,$link);		
	}
}
