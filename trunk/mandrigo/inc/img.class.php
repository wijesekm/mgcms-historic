<?php
/**********************************************************
    img.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/16/07

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

class img{
	
	var $width;
	var $height;
	var $image;
	var $type;
	
	function img{}
	
	function img_read($file){
		$tmp=getimagesize($file);
		$this->width=$tmp[0];
		$this->height=$tmp[1];
		
		$ext=exif_imagetype($file);
		$tmp_img=false;
		switch($ext){
			case IMAGETYPE_JPEG:
				$tmp_img=@imagecreatefromjpeg($file);
			break;
			case IMAGETYPE_GIF:
				$tmp_img=@imagecreatefromgif($file);
			break;
			case IMAGETYPE_PNG:
				$tmp_img=@imagecreatefrompng($file);
			break;
			default:
				return false;
			break;
		};
		if($tmp_img==false){
			return false;
		}
		$this->image=$ext;
		$this->image=$tmp_img;
		@imagedestroy($tmp_img);
		return true;
	}
	
	function img_create($width,$height,$type=IMAGETYPE_JPEG){
		$this->width=$width;
		$this->height=$height;
		$tmp_img=@imagecreatetruecolor($width,$height);
		if($tmp_img==false){
			return false;
		}
		$this->type=$type;
		$this->image=$tmp_img;
		@imagedestroy($tmp_img);
		return true;
	}
	
	function img_write($params=array(),$file=""){
		switch($this->type){
			case IMAGETYPE_JPEG:
				if(!$params["quality"]){
					$params["quality"]=80;
				}
				header('Content-type: image/jpeg');
				header('Content-Length: ' . strlen($this->image));
				imagejpeg($this->image,$file,$params["quality"]);
				die();
			break;
			case IMAGETYPE_GIF:
				header('Content-type: image/gif');
				header('Content-Length: ' . strlen($this->image));
				imagegif($this->image,$file);
				die();
			break;
			case IMAGETYPE_PNG:
				if(!$params["quality"]){
					$params["quality"]=4;
				}
				header('Content-type: image/png');
				header('Content-Length: ' . strlen($this->image));
				imagepng($this->image,$file,$params["quality"]);
				die();
			break;
			default:
				return false;
			break;
		}
	}
	
	function img_presizelimit($width_max,$height_max){
		if($width_max > $this->width){
			$width_max=$this->width;
		}
		if($heigh_max > $this->height){
			$height_max = $this->height;
		}
		return $this->img_presize($width_max,$height_max);
	} 
	
	function img_presize($width_max,$height_max){
		$ratio=$this->width/$this->height;
		$new_ratio=$width_max/$height_max;
		if ($new_ratio > $ratio){
   			$width_max = $height_max*$ratio;
		} 
		else {
   			$height_max = $width_max/$ratio;
		}
		return $this->img_resize($width_max,$height_max);
	}
	
	function img_resize($new_width,$new_height){
		$tmp_img=@imagecreatetruecolor($new_width, $new_height);
		if(!$tmp_img){
			return false;
		}
		if(!imagecopyresampled($tmp_img, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height)){
			return false;
		}
		$this->image=$tmp_img;
		$this->width=$new_width;
		$this->height=$new_height;
		@imagedestroy($tmp_img);
		return true;
	}
	
	function img_gdversion(){
		static $gd_version_number = null;
		if($gd_version_number === null){
			ob_start();
			phpinfo(8);
			$module_info = ob_get_contents();
 			ob_end_clean();
			if(preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i", $module_info, $matches)){
				$gd_version_number = $matches[1];
 			}
			else{
 				$gd_version_number = 0;
 			}
		}
		return $gd_version_number;		
	}
}