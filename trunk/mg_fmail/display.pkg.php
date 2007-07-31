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
			return false;
		}
        $attrib='src="'.$GLOBALS['MANDRIGO']["SITE"]['IMG_URL']."/mg_fmail/".'dot.jpg" alt="*" border="0"';
        $this->config['star']=mb_ereg_replace('\{ATTRIB\}',$attrib,$GLOBALS['MANDRIGO']['HTML']['IMG']);
       	$this->config["nostar"]=$GLOBALS["MANDRIGO"]["HTML"]["BR"].$GLOBALS["MANDRIGO"]["HTML"]["BR"];
		if($this->config['fmail_usecaptcha']==1){
			if(!$this->fcaptcha=new captcha($id)){
				return false;
			}
		}
        $this->tpl=new template();
        $file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTPAGE']['NAME'].'_'.$i.'.'.TPL_EXT;
		if(!$this->tpl->tpl_load($file,"overview")){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(,"display");
			return false;
		}
	}
    function fm_display($id,$status=array()){

        //gets the email address and name to mail to
        $to_name="";
        $to_email="";
        $r_email="";
        
        if(mb_eregi("^[0-9]+$",$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"])){
			$list_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_FEMAIL_LIST,"",array(array("fmail_id","=",$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"])));
			if($list_data["fmail_uid"]){
			 	if($list_data["fmail_uid"]==$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ID"]){
					$to_name=$GLOBALS["MANDRIGO"]["CURRENTUSER"]["FNAME"]." ".$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LNAME"];
					$to_email=$GLOBALS["MANDRIGO"]["CURRENTUSER"]["EMAIL"];
					if($list_data["fmail_returnaddr"]){
						$r_email=$list_data["fmail_returnaddr"];
					}
					else{
						$r_email=$GLOBALS["MANDRIGO"]["CURRENTUSER"]["EMAIL"];
					}
				}
				else{
					$cuser_acct=new account($GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"]);
					$user_data=$cuser_acct->ac_userdata();
					$to_name=$user_data["FNAME"]." ".$user_data["LNAME"];
					$to_email=$user_data["EMAIL"];
					if($list_data["fmail_returnaddr"]){
						$r_email=$list_data["fmail_returnaddr"];
					}
					else{
						$r_email=$user_data["EMAIL"];
					}
				}
			}
			else if($list_data["fmail_name"]){
				$to_name=$list_data["fmail_name"];
				$to_email=$list_data["fmail_addr"];
				$r_email=$list_data["fmail_returnaddr"];
			}
		}
		else if($GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"]){
			$to_name=$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"];
			$to_email=$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"];
			$r_email=$GLOBALS["MANDRIGO"]["VARS"]["EMAIL_ADDRESS"];
		}

        if(!$to_name||!$to_email){
			return false;
		}
		if(!$r_email){
			$r_email=$to_email;
		}
		
		if($this->config['fmail_usecaptcha']==1){
			$ca_id=$this->fcaptcha->ca_genca();
		}
		
        $parse_vars=array("MAIL_S_NAME",$to_name
                            	,"MAIL_S_FORM_EMAIL",$to_email
                                ,"MAIL_S_DISP_EMAIL",$r_email
								,"MAIL_CAID",$ca_id
								,"MAIL_IMG",$GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"].TMP_IMG.$ca_id.".jpg");
								
        $errored=false;
		for($i=0;$i<;$i++){
			if($status["MAIL"]["S$i"]===true){
				$parse_vars=$this->fm_appendarray($parse_vars,array("MAIL_STAR$i",$this->config["star"]));
				$errored=true;
			}
			else{
				$parse_vars=$this->fm_appendarray($parse_vars,array("MAIL_STAR$i",$this->config["nostar"]));
			}
		}
		
		if($errored){
			$parse_vars=$this->fm_appendarray($parse_vars,array("TOP_MSG",$GLOBALS["MANDRIGO"]["LANGUAGE"]["MG_FMAIL_IERROR"]));
		}
		
		if($status["INTERNAL"]["MFAIL"]===true){
			$parse_vars=$this->fm_appendarray($parse_vars,array("TOP_MSG",$GLOBALS["MANDRIGO"]["LANGUAGE"]["MG_FMAIL_IERROR"]));
			$errored=true;
		}
		else if($status["SENT"]){
			$parse_vars=$this->fm_appendarray($parse_vars,array("TOP_MSG",$GLOBALS["MANDRIGO"]["LANGUAGE"]["MG_FMAIL_SENT"]));			
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
        $errors=array();

        //Error Level: 0 - dont fail on anything
        //             1 - fail on no message
        //             2 - fail on no message and name
        //             3 - fail on no message, name, and email
        //             4 - fail on no message, name, email, and subject
        if($this->config["E_LEVEL"]>=1&&(!$GLOBALS["HTTP_POST"]["M_MESSAGE"]||$GLOBALS["HTTP_POST"]["M_MESSAGE"]==BAD_DATA)){
            $errors["MAIL_STAR1"]=true;
            $GLOBALS["HTTP_POST"]["M_MESSAGE"]="";
        }
        if($this->config["E_LEVEL"]>=2&&(!$GLOBALS["HTTP_POST"]["M_USER_NAME"]||$GLOBALS["HTTP_POST"]["M_USER_NAME"]==BAD_DATA)){
            $errors["MAIL_STAR2"]=true;
            $GLOBALS["HTTP_POST"]["M_USER_NAME"]="";
        }
        if($this->config["E_LEVEL"]>=3&&(!$GLOBALS["HTTP_POST"]["M_USER_EMAIL"]||$GLOBALS["HTTP_POST"]["M_USER_EMAIL"]==BAD_DATA)){
            $errors["MAIL_STAR3"]=true;
            $GLOBALS["HTTP_POST"]["M_USER_EMAIL"]="";
        }
        if($this->config["E_LEVEL"]>=4&&(!$GLOBALS["HTTP_POST"]["M_SUBJECT"]||$GLOBALS["HTTP_POST"]["M_SUBJECT"]==BAD_DATA)){
            $errors["MAIL_STAR4"]=true;
            $GLOBALS["HTTP_POST"]["M_SUBJECT"]="";
        }
        if(!$GLOBALS["HTTP_GET"]["MAIL_ADDR"]){
            return false;
        }
        if($this->config["FORM_VALIDATE"]==1){
         	$validate=new captcha($this->db,$id);
         	if(!$validate->ca_checkca()){
				$errors["MAIL_STAR5"]=true;	
			}
		}
        //if we have any errors go back to display screen and show errors
        if(count($errors)){
            $errors["FAIL"]=true;
            $errors["MAIL"]=false;
            return $this->fm_display($id,$errors);
        }
        //gets e-mail addr for user who is getting the e-mail sent to
        $rc_email='';
        $rc_fullname='';
        if(!eregi("@",$GLOBALS["HTTP_GET"]["MAIL_ADDR"])){
            if(!$sql_result=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_EMAIL_LIST,"",array(array("email_id","=",$GLOBALS["HTTP_GET"]["MAIL_ADDR"])))){
                return false;
            }
            if($sql_result["user_id"]){
                if(!$sql_result1=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$sql_result["user_id"])))){
                    return false;
                }
                    $rc_email=$sql_result1["user_email"];
                    $tmp=explode(";",$sql_result1["user_real_name"]);
	                $rc_fullname=$tmp[0]." ";
	                if($tmp[1]){
						$rc_fullname.=$tmp[1]." ";
					}
					$rc_fullname.=$tmp[2];
            }
            else{
                $rc_fullname=$sql_result["user_fullname"];
                $rc_email=$sql_result["user_email"];
            }
        }
        else{
            $rc_email=$GLOBALS["HTTP_GET"]["MAIL_ADDR"];
            $rc_fullname=$GLOBALS["HTTP_GET"]["MAIL_ADDR"];
        }
        
        //makes the message and then sends it
        $ev=new envelope($id,$this->db);

        //subject
        $subj=$this->config["SUBJ_PREFIX"]." ".((isset($GLOBALS["HTTP_POST"]["M_SUBJECT"]))?$GLOBALS["HTTP_POST"]["M_SUBJECT"]:$sql_result["default_subj"]);
        $ev->ev_addsubject(ereg_replace("\n","",$subj));
        
        //to
        $ev->ev_addrecipient((string)$rc_email,(string)$rc_fullname);
        $ev->ev_addsender($GLOBALS["HTTP_POST"]["M_USER_EMAIL"],$GLOBALS["HTTP_POST"]["M_USER_NAME"]);

		$tpl=new template();
        $tpl->load("",$this->config["EMAIL_MSG"]);
        $eparse_vars = array("MESSAGE",$GLOBALS["HTTP_POST"]["M_MESSAGE"]
        					,"MANDRIGO_VERSION",$GLOBALS["SITE_DATA"]["MANDRIGO_VER"]
							,"SITE_NAME",$GLOBALS["SITE_DATA"]["SITE_NAME"]
							,"SITE_URL",$GLOBALS["SITE_DATA"]["SITE_URL"]
							,"TO",$rc_fullname
							,"TO_EMAIL",$rc_email
							,"FROM",$GLOBALS["HTTP_POST"]["M_USER_NAME"]
							,"FROM_EMAIL",$GLOBALS["HTTP_POST"]["M_USER_EMAIL"]
							,"DATE",date($this->config["DATE_FORMAT"])
							);
        $tpl->pparse($eparse_vars,false);
        $send_msg=$tpl->return_template();
        $ev->ev_addbody((string)$send_msg);
        if(!$ev->ev_send()){
			return $this->fm_display($id,array("FAIL"=>true,"MAIL"=>false,"UNKNOWN"=>true));
		}
		return $this->fm_display($id,array("FAIL"=>false,"MAIL"=>true));
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
}
?>
