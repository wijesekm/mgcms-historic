<?php
/**********************************************************
    display.pkg.php
    mg_fmail version 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/31/07

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

class mg_fmail{

    var $config;
    var $tpl;
    var $fcaptcha;

    function mg_fmail($id){
     	if(!$this->config=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_FMAIL_DATA,"",array(array("page_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"],DB_AND),array("part_id","=",$id)))){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(430,"sql");
			return false;
		}
        $attrib='src="'.$GLOBALS['MANDRIGO']["SITE"]['IMG_URL']."/mg_fmail/".'dot.jpg" alt="*" style="border:0;background:0;"';
        $this->config['star']=mb_ereg_replace('\{ATTRIB\}',$attrib,$GLOBALS['MANDRIGO']['HTML']['IMG']);
       	$this->config["nostar"]="";
		if($this->config['fmail_usecaptcha']==1){
			if(!$this->fcaptcha=new captcha($id)){
				return false;
			}
		}
        $this->tpl=new template();
        $file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['NAME'].'_'.$id.'.'.TPL_EXT;
		if(!$this->tpl->tpl_load($file,"overview")){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(150,"display");
			return false;
		}
	}
    function fm_display($status=array()){
        $sdata=$this->fm_getrecipemail();
		if(!$sdata){
			return $GLOBALS["MANDRIGO"]["LANGUAGE"]["MG_FMAIL_NOEMAIL"];
		}
	
		if($this->config['fmail_usecaptcha']==1){
			$ca_id=$this->fcaptcha->ca_genca();
		}
		
        $parse_vars=array("FMAIL_SNAME",$sdata[0]
        				 ,"FORM_ACTION",$this->fm_genlink(array("p",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],"a","p"))
                         ,"FMAIL_SEMAIL",$sdata[1]
						 ,"FMAIL_CAID",$ca_id
						 ,"FMAIL_CAIMG",$GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"].TMP_IMG.$ca_id.".jpg");
								
        $errored=false;
		for($i=1;$i<6;$i++){
			if($status["MAIL"]["S$i"]===true){
				$parse_vars=$this->fm_appendarray($parse_vars,array("FMAIL_STAR$i",$this->config["star"]));
				$errored=true;
			}
			else{
				$parse_vars=$this->fm_appendarray($parse_vars,array("FMAIL_STAR$i",$this->config["nostar"]));
			}
		}
		
		if($errored){
			$parse_vars=$this->fm_appendarray($parse_vars,array("FMAIL_TOP",$GLOBALS["MANDRIGO"]["LANGUAGE"]["MG_FMAIL_IERROR"]
															    ,"FMAIL_PNAME",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]
																,"FMAIL_PMAIL",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"]
																,"FMAIL_PSUBJ",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_SUBJ"]
																,"FMAIL_PMSG",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]));
		}
		
		if($status["SENT"]===true){
			$parse_vars=$this->fm_appendarray($parse_vars,array("FMAIL_TOP",$GLOBALS["MANDRIGO"]["LANGUAGE"]["MG_FMAIL_SENT"]));			
		}
		
		if($errored){
			$parse_vars=$this->fm_appendarray($parse_vars,array("FMAIL_NAME",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"],
																"FMAIL_ADDR",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"],
																"FMAIL_SUBJ",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_SUBJ"],
																"FMAIL_MSG",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]));
		}
		$this->tpl->tpl_parse($parse_vars,"overview",2,false);
		return $this->tpl->tpl_return("overview");
    }
    function fm_mail($id){
        $status=array();
		$status["MAIL"]["S1"]=false;
		$status["MAIL"]["S2"]=false;
		$status["MAIL"]["S3"]=false;
		$status["MAIL"]["S4"]=false;
		$status["MAIL"]["S5"]=false;
		$status["SENT"]=false;
		
        //Error Level: 0 - dont fail on anything
        //             1 - fail on no message
        //             2 - fail on no message and name
        //             3 - fail on no message, name, and email
        //             4 - fail on no message, name, email, and subject
        switch($this->config["fmail_elevel"]){
			case 4:
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]==BAD_DATA){
					$status["MAIL"]["S1"]=true;	
				}
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]==BAD_DATA){
					$status["MAIL"]["S2"]=true;	
				}
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"]==BAD_DATA){
					$status["MAIL"]["S3"]=true;	
				}
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_SUBJ"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_SUBJ"]==BAD_DATA){
					$status["MAIL"]["S4"]=true;	
				}			
			break;
			case 3:
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]==BAD_DATA){
					$status["MAIL"]["S1"]=true;	
				}
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]==BAD_DATA){
					$status["MAIL"]["S2"]=true;	
				}
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"]==BAD_DATA){
					$status["MAIL"]["S3"]=true;	
				}
			break;
			case 2:
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]==BAD_DATA){
					$status["MAIL"]["S1"]=true;	
				}
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]==BAD_DATA){
					$status["MAIL"]["S2"]=true;	
				}
			break;
			case 1:
				if(!$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]||$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]==BAD_DATA){
					$status["MAIL"]["S1"]=true;	
				}
			break;
			case 0:
			default:
			
			break;
		};
		
		if($GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]==BAD_DATA){
			$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]="";
		}
		if($GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"]==BAD_DATA){
			$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"]="";
		}
		if($GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_SUBJ"]==BAD_DATA){
			$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_SUBJ"]="";
		}
		if($GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]==BAD_DATA){
			$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"]="";
		}
		
		if($this->config['fmail_usecaptcha']==1){
			if(!$this->fcaptcha->ca_checkca()){
				$status["MAIL"]["S5"]=true;
			}
		}

		$sdata=$this->fm_getrecipemail();
		
		if(!$sdata){
			return false;
		}
				
		if($status["MAIL"]["S1"]||$status["MAIL"]["S2"]||$status["MAIL"]["S3"]||$status["MAIL"]["S4"]||$status["MAIL"]["S5"]){
			return $this->fm_display($status);
		}

        $ev=new envelope($id);
        $subj=isset($GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_SUBJ"])?$this->config["fmail_subjprefix"]." ".$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_SUBJ"]:$this->config["fmail_dsubject"];
        $ev->ev_addsubject($subj);

		$ev->ev_addrecipient((string)$sdata[1],(string)$sdata[0]);
		$ev->ev_addsender($GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"],$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"]);
		
		$body=new template();
		$body->tpl_load($this->config["fmail_emailtpl"],"email",false);
		$eparse_vars=array("MESSAGE",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_MSG"],
						   "MANDRIGO_VERSION",$GLOBALS["MANDRIGO"]["SITE"]["MANDRIGO_VER"],
						   "SITE_NAME",$GLOBALS["MANDRIGO"]["SITE"]["SITE_NAME"],
						   "SITE_URL",$GLOBALS["MANDRIGO"]["SITE"]["SITE_URL"],
						   "TO",$sdata[0],
						   "TO_EMAIL",$sdata[1],
						   "FROM",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_NAME"],
						   "FROM_EMAIL",$GLOBALS["MANDRIGO"]["VARS"]["MG_FMAIL_ADDR"],
						   "DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]),
						   "TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]));
		$body->tpl_parse($eparse_vars,"email",1,false);
		$ev->ev_addbody($body->tpl_return("email"));
		
        if(!$ev->ev_send()){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(151,"display");
			return false;
		}
		else{
			$status["SENT"]=true;
		}
		return $this->fm_display($status);
    }
    function fm_getrecipemail(){
     
        $to_name="";
        $to_email="";
        
		if(!$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"]||$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"]==BAD_DATA){
			$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"]=1;
		}
        if(mb_eregi("^[0-9]+$",$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"])){
			$list_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_FEMAIL_LIST,"",array(array("fmail_id","=",$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"])));
			if($list_data["fmail_uid"]){
			 	if((int)$list_data["fmail_uid"]==$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ID"]){
					$to_name=$GLOBALS["MANDRIGO"]["CURRENTUSER"]["FNAME"]." ".$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LNAME"];
					$to_email=$GLOBALS["MANDRIGO"]["CURRENTUSER"]["EMAIL"];
				}
				else{
					$cuser_acct=new account($list_data["fmail_uid"]);
					$user_data=$cuser_acct->ac_userdata();
					$to_name=$user_data["FNAME"]." ".$user_data["LNAME"];
					$to_email=$user_data["EMAIL"];
				}
			}
			else if($list_data["fmail_name"]){
				$to_name=$list_data["fmail_name"];
				$to_email=$list_data["fmail_addr"];
			}
		}
		else if($GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"]){
			$to_name=$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"];
			$to_email=$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"];
		}

        if(!$to_name||!$to_email){
			return false;
		}
		return array($to_name,$to_email);
	}
    function fm_appendarray($a1,$a2){
		$size1=count($a1);
		$size2=count($a2);
		$soq=$size1+$size2;
		for($i=$size1;$i<$soq;$i++){
			$a1[$i]=$a2[$i-($size1)];
		}
		return $a1;
	}
    function fm_genlink($url_data){
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
}