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

class form_validator{
  
  	var $db;
  	var $config;

	function form_validator(&$sql,$len){
		$this->db=$sql;
		$this->config["len"]=$len;

	}  
	function make($id,$app){
		$rnd=$this->random_string();
		
		$image = ImageCreate(20*$this->config["len"]+20,30);	
		$background_color = imagecolorallocate($image, 255, 255, 255);
		for($i=0;$i<$this->config["len"];$i++){
			$col=$this->rand_color();	
			imagechar($image, imageloadfont($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"].$this->rand_font()), (20*$i)+5+$this->rand_offset(), 1+$this->rand_offset(),$rnd[$i], imagecolorallocate($image,$col[0],$col[1],$col[2]));
		}
		imagepng($image,$GLOBALS["MANDRIGO_CONFIG"]["IMG_PATH"].TMP_IMG."/".$app."_".$id.".png");
		imagedestroy($image);
		if(!$this->db->db_update(DB_INSERT,TABLE_PREFIX.TABLE_TEMP,array("$app.$id",$rnd))){
			return false;
		}
		return true;
	}
	function rand_color(){
		return array(rand(0,200),rand(0,200),rand(0,200));
	}
	function rand_offset(){
		return rand(0,10);		
	}
	function rand_font(){
		$rn=rand(0,3);
		switch($rn){
			case 0:
				$file="fonts/Arial10.gdf";
			break;
			case 1:
				$file="fonts/Arial12.gdf";
			break;
			case 2:
				$file="fonts/Arial14.gdf";
			break;
			default:
				$file="fonts/Arial16.gdf";
			break;	
		};
		return $file;
	}
	function check($str,$id,$app){
		if(!$db_str=$this->db->db_fetchresult(TABLE_PREFIX.TABLE_TEMP,"field_value",array(array("field_name","=","$app.$id")))){
			return false;
		}
		$str=ereg_replace("\n","",$str);
		$this->db->db_update(DB_REMOVE,TABLE_PREFIX.TABLE_TEMP,"",array(array("field_name","=","$app.$id")));	
		unlink($GLOBALS["MANDRIGO_CONFIG"]["IMG_PATH"].TMP_IMG."/".$app."_".$id.".png");
		if(strtoupper($str)!=strtoupper($db_str)){
			return false;
		}	
		return true;
	}
	function random_string(){
		$string="";
		for($i=0;$i<$this->config["len"];$i++){
			$rn=rand(0,25);
			switch($rn){
				case 0:
					$string.="A";
				break;
				case 1:
					$string.="B";
				break;
				case 2:
					$string.="C";
				break;
				case 3:
					$string.="D";
				break;
				case 4:
					$string.="E";
				break;	
				case 5:
					$string.="F";
				break;
				case 6:
					$string.="G";
				break;
				case 7:
					$string.="H";
				break;
				case 8:
					$string.="I";
				break;
				case 9:
					$string.="J";
				break;	
				case 10:
					$string.="K";
				break;
				case 11:
					$string.="L";
				break;
				case 12:
					$string.="M";
				break;
				case 13:
					$string.="N";
				break;
				case 14:
					$string.="O";
				break;	
				case 15:
					$string.="P";
				break;
				case 16:
					$string.="Q";
				break;
				case 17:
					$string.="R";
				break;
				case 18:
					$string.="S";
				break;
				case 19:
					$string.="T";
				break;	
				case 20:
					$string.="U";
				break;
				case 21:
					$string.="V";
				break;
				case 22:
					$string.="W";
				break;
				case 23:
					$string.="X";
				break;		
				case 24:
					$string.="Y";
				break;
				default:
					$string.="Z";
				break;		
			};
		}
		return $string;
	}
}