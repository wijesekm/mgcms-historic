<?php
/**********************************************************
    page.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 12/24/05

	Copyright (C) 2005  Kevin Wijesekera

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

class template{

    var $tpl;
    
    function load($path,$file="",$deliminator=""){
        if(!$path){
            if(!$deliminator){
                $this->tpl[0]=$file;
            }
            else{
                $this->tpl=explode($deliminator,$file);
            }
        }
        else{
            if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                $f=fopen($path,"r");
            }
            else{
                if(!(@$f=fopen($path,"r"))){
                    return false;
                }
            }
            $tmp="";
            while(!feof($f)){
                $tmp.=fgets($f);
            }
            fclose($f);
            if(!$deliminator){
                $this->tpl[0]=$tmp;
            }
            else{
                $this->tpl=explode($deliminator,$tmp);
            }
        }
        return true;
    }
    function pparse($vars=array(),$comp=true,$vparse=true,$tplcomp=false){
        $sot=count($this->tpl);
        for($i=0;$i<$sot;$i++){
          	if($tplcomp[$i]||!$tplcomp){
	            if($comp){
	                $this->tpl[$i]=$this->compile($vars,$this->tpl[$i]);
	            }
	            if($vparse){
	                $this->tpl[$i]=$this->vparse($vars,$this->tpl[$i]);
	            }			    
			}
        }
        $this->regester_type();
        return true;
    }
    function return_template($pos=0){
        return $this->tpl[$pos];
    }
    function vparse($vars,$string){
        $sov=count($vars);
        if(!$sov%2){
            return false;
        }
        for($i=0;$i<$sov-1;$i+=2){
            $string=ereg_replace("{".$vars[$i]."}",$vars[$i+1],$string);
        }
        $string=eregi_replace("[{]+[a-z0-9_-]+[}]","",$string);
        return $string;
    }
    function compile($vars,$string){

        if(!ereg(MANDRIGO_CODE_BLOCK,$string)){
            return $string;
        }
        $tmp=explode(MANDRIGO_CODE_BLOCK,$string);
        $sov=count($tmp);
        if(!$sov%2){
            return false;
        }
        $string="";
        for($i=1;$i<$sov-1;$i+=2){
            $string.=$tmp[$i-1];
            $tmp_=$this->vparse($vars,$tmp[$i]);
            $print_string="";
            eval($tmp_);
            $string.=$print_string;
            }
        return $string;
    }
    function regester_type(){
		if(!$GLOBALS["LANGUAGE"]["REG"]){
		  	if($GLOBALS["LANGUAGE"]["SET_ENCODING"]){
				header("Content-type: ".$GLOBALS["LANGUAGE"]["CONTENT_TYPE"]." charset=".$GLOBALS["LANGUAGE"]["CHARSET"]);
			}
			else{
				header("Content-type: ".$GLOBALS["LANGUAGE"]["CONTENT_TYPE"]);
			}
			$GLOBALS["LANGUAGE"]["REG"]=true;
		}
	}
}

?>
