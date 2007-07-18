<?php
/**********************************************************
    display.pkg.php
    f_mail ver 0.6.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 08/24/06

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

class f_mail_display{

    var $config;
    var $db;
    var $pparse_vars;
    var $def_error;

    function f_mail_display(&$sql_db){
        $this->db=$sql_db;
        $attrib='src="'.$GLOBALS['SITE_DATA']['IMG_URL'].'dot.jpg" alt="*" border="0"';
        $this->config['STAR']=ereg_replace('{ATTRIB}',$attrib,$GLOBALS['HTML']['IMG']);
	}
    function fm_load($i){
      	if(!$sql_result=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_EMAIL_DATA,"",array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"],DB_AND),array("part_id","=",$i)))){
            return false;
        }
        $this->config["SUBJ_PREFIX"]=$sql_result["subj_prefix"];
        $this->config["DEFAULT_SUBJ"]=$sql_result["default_subj"];
        $this->config["HTML_ON"]=$sql_result["html_on"];
        $this->config["BBCODE_ON"]=$sql_result["bbcode_on"];
        $this->config["EMAIL_MSG"]=$sql_result["email_msg"];
        $this->config["E_LEVEL"]=$sql_result["error_level"];
        $this->config["SEND_TYPE"]=$sql_result["send_type"];
        $this->config["FORM_VALIDATE"]=$sql_result["form_validate"];
        $this->config["ALERT_STYLE"]=$sql_result["alert_style"];
        $this->config["DATE_FORMAT"]=$sql_result["date_format"];
    }
    function fm_display($id,$errors=array()){
      	if($this->config['FORM_VALIDATE']==1){
      	 	$validate=new captcha($this->db,$id);
	      	if(!$ca_id=$validate->ca_genca()){
				return false;
			}
      	}
      	
        //gets the email address and name to mail to
        $to_name="";
        $to_email="";
        $r_email="";
        
        if(!$sql_result=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_EMAIL_LIST,"",array(array("email_id","=",$GLOBALS["HTTP_GET"]["MAIL_ADDR"])))){
            if(!$sql_result=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_EMAIL_LIST,"",array(array("user_email","=",$GLOBALS["HTTP_GET"]["MAIL_ADDR"])))){
                $to_email=$GLOBALS["HTTP_GET"]["MAIL_ADDR"];
                $to_name=$GLOBALS["HTTP_GET"]["MAIL_ADDR"];
                $r_email=$GLOBALS["HTTP_GET"]["MAIL_ADDR"];
            }
            $to_email=$sql_result["email_id"];
            if(!$sql_result["user_id"]){
                $r_email=$sql_result["user_email"];
                $to_name=$sql_result["user_fullname"];
            }
            else{
                if(!$sql_result1=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$sql_result["user_id"])))){
                    return false;
                }
                $r_email=$sql_result1["user_email"];
                $tmp=explode(";",$sql_result1["user_real_name"]);
                $to_name=$tmp[0]." ";
                if($tmp[1]){
					$to_name.=$tmp[1]." ";
				}
				$to_name.=$tmp[2];
            }
        }
        else{
            $to_email=$sql_result["email_id"];
            if(!$sql_result["user_id"]){
                $r_email=$sql_result["user_email"];
                $to_name=$sql_result["user_fullname"];
            }
            else{
                if(!$sql_result1=$this->db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$sql_result["user_id"])))){
                    return false;
                }
                $r_email=$sql_result1["user_email"];
                $tmp=explode(";",$sql_result1["user_real_name"]);
                $to_name=$tmp[0]." ";
                if($tmp[1]){
					$to_name.=$tmp[1]." ";
				}
				$to_name.=$tmp[2];
            }
        }
            $this->pparse_vars = array("MAIL_S_NAME",$to_name
                                ,"MAIL_S_FORM_EMAIL",$to_email
                                ,"MAIL_S_DISP_EMAIL",$r_email
								,"MAIL_CAID",(string)$ca_id
								,"MAIL_IMG",$GLOBALS["SITE_DATA"]["IMG_URL"].TMP_IMG.$ca_id.".jpg");
        if($errors["FAIL"]&&!$errors["MAIL"]){
			if($errors["MAIL_STAR1"]){
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR1",$this->config["STAR"]),$this->pparse_vars);  
			}
			else{
			 	$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR1",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars);   
			}
			if($errors["MAIL_STAR2"]){
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR2",$this->config["STAR"]),$this->pparse_vars);  
			}
			else{
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR2",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars);    
			}
			if($errors["MAIL_STAR3"]){
			 	$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR3",$this->config["STAR"]),$this->pparse_vars); 
			}
			else{
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR3",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars);    
			}
			if($errors["MAIL_STAR4"]){
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR4",$this->config["STAR"]),$this->pparse_vars);
			}
			else{
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR4",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars);    
			}
			if($errors["MAIL_STAR5"]){
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR5",$this->config["STAR"]),$this->pparse_vars);
			}
			else{
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR5",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars);    
			}
			if(!$errors["UNKNOWN"]){
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_TOP_ERROR",$GLOBALS["LANGUAGE"]["F_MAIL_ERROR_ALERT"].$GLOBALS["HTML"]["BR"]),$this->pparse_vars);	
			}
			else{
				$this->pparse_vars=$this->fm_mergearray(array("MAIL_TOP_ERROR",$GLOBALS["LANGUAGE"]["F_MAIL_INTERNAL"].$GLOBALS["HTML"]["BR"]),$this->pparse_vars);
			}
			$vars=array("MAIL_U_NAME",$GLOBALS["HTTP_POST"]["M_USER_NAME"]
                        ,"MAIL_U_EMAIL",$GLOBALS["HTTP_POST"]["M_USER_EMAIL"]
                        ,"MAIL_SUBJECT",$GLOBALS["HTTP_POST"]["M_SUBJECT"]
                        ,"MAIL_MESSAGE",$GLOBALS["HTTP_POST"]["M_MESSAGE"]);
            $this->pparse_vars=$this->fm_mergearray($vars,$this->pparse_vars);
		}
		else if(!$errors["FAIL"]&&$errors["MAIL"]){
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_TOP_ERROR",$GLOBALS["LANGUAGE"]["F_MAIL_SENT"].$GLOBALS["HTML"]["BR"]),$this->pparse_vars);	
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR1",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR2",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR3",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR4",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 	
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR5",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 	
		}
		else{
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR1",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR2",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR3",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR4",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 	
			$this->pparse_vars=$this->fm_mergearray(array("MAIL_STAR5",$GLOBALS["HTML"]["SPACE"].$GLOBALS["HTML"]["SPACE"]),$this->pparse_vars); 	
		}
        $tmp = new template();
        $tmp->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].$GLOBALS["PAGE_DATA"]["DATAPATH"].$GLOBALS["PAGE_DATA"]["ID"]."_".$id."_email.".TPL_EXT);
		return $tmp->return_template();
    }
    function fm_retvars(){
        return $this->pparse_vars;
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
    function fm_mergearray($a1,$a2){
		$new_array=array();
		$j=0;
		for($i=0;$i<count($a1);$i++){
			$new_array[$j]=$a1[$i];
			$j++;
		}
		for($i=0;$i<count($a2);$i++){
			$new_array[$j]=$a2[$i];
			$j++;
		}
		return $new_array;
	}
}
?>