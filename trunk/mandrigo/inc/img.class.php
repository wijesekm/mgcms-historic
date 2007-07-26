<?php
/**********************************************************
    img.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/25/07

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

    //
    //constructor img()
    //
    //Initializes the img script
    //
    //returns object on sucess or false on fail		
	function img(){
		if($this->img_gdversion() < 2){
			return false;
		}
	}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	  	
	
	//
	//Read/Write Functions
	//
 	
    //
    //public function img_read($file)
    //
    //Loads an image from a file
    //INPUTS:
    //$file		-	file to read from [string]
    //
	//returns true on sucess or false on fail
	function img_read($file){
		$tmp=getimagesize($file);
		$this->width=$tmp[0];
		$this->height=$tmp[1];
		
		$ext=exif_imagetype($file);
		switch($ext){
			case IMAGETYPE_JPEG:
				$this->image=@imagecreatefromjpeg($file);
			break;
			case IMAGETYPE_GIF:
				$this->image=@imagecreatefromgif($file);
			break;
			case IMAGETYPE_PNG:
				$this->image=@imagecreatefrompng($file);
			break;
			default:
				return false;
			break;
		};
		if($this->image==false){
			return false;
		}
		$this->type=$ext;
		return true;
	}
 	
    //
    //public function img_create($width,$height,$type=IMAGETYPE_JPEG)
    //
    //Makes a new imeage
    //INPUTS:
    //$width		-	image width [int]
    //$height		-	image height [int]
    //$type			-	type of image from standard php image constants [string] (default: IMAGETYPE_JPEG)
    //
	//returns true on sucess or false on fail	
	function img_create($width,$height,$type=IMAGETYPE_JPEG){
		$this->width=$width;
		$this->height=$height;
		if($type==IMAGETYPE_GIF){
			$this->image=@imagecreate($width,$height);
		}
		else{
			$this->image=@imagecreatetruecolor($width,$height);
		}
		if($this->image==false){
			return false;
		}
		$this->type=$type;
		return true;
	}
 	
    //
    //public function img_write($params=array(),$file="")
    //
    //Write an image to a file or the screen if no filepath is given
    //INPUTS:
    //$params		-	parameters [array:attributes (quality)] (default: )
    //$file			-	file path [string] (default: )	
    //
	//returns true on sucess or false on fail		
	function img_write($params=array(),$file=""){
		switch($this->type){
			case IMAGETYPE_JPEG:
				if(!$params["quality"]){
					$params["quality"]=80;
				}
				if(!$file){
					header('Content-type: image/jpeg');
					header('Content-Length: ' . strlen($this->image));
					imagejpeg($this->image,"",$params["quality"]);
					imagedestroy($this->image);
					die();				
				}
				else{
					imagejpeg($this->image,$file,$params["quality"]);	
				}

			break;
			case IMAGETYPE_GIF:
				if(!$file){
					header('Content-type: image/gif');
					header('Content-Length: ' . strlen($this->image));
					imagegif($this->image,$file);
					imagedestroy($this->image);
					die();			
				}
				else{
					imagegif($this->image,$file);
				}
			break;
			case IMAGETYPE_PNG:
				if(!$params["quality"]){
					$params["quality"]=4;
				}
				if(!$file){
					header('Content-type: image/png');
					header('Content-Length: ' . strlen($this->image));
					imagepng($this->image,"",$params["quality"]);
					imagedestroy($this->image);
					die();			
				}
				else{
					imagepng($this->image,$file,$params["quality"]);
				}
			break;
			default:
				return false;
			break;
		}
		return true;
	}
	
	//
	//Draw Functions
	//
		
    //
    //public function img_line($x,$y,$color,$ctype)
    //
    //Draws a line on the current image
    //INPUTS:
    //$x			-	2 x positions [array]
    //$y			-	2 y positions [array]
    //$color		-	rgb color numbers [array:attributes (r,g,b)]
    //$ctype		-	color type: close,default [string] (default: )
    //
	//returns true on sucess or false on fail	
	function img_line($x,$y,$color,$ctype){
		$color=$this->img_getcolor($color,$ctype);
		@imageline($this->image,$x[0],$y[0],$x[1],$y[1],$color);
	}
	
    //
    //public function img_ttftext($string,$size,$angle,$x,$y,$color,$ttf_file,$ctype="")
    //
    //Draws a string of text on the current image
    //INPUTS:
    //$string		-	text to draw [string]
    //$size			-	point size of text [int]
    //$angle		-	angle of rotation in degrees [int]
    //$x			-	x position of base of first character
    //$y			-	y position of base of first character
    //$color		-	rgb color numbers [array:attributes (r,g,b)]
    //$ttf_file		-	file path to ttf font file to use
    //$ctype		-	color type: close,default [string] (default: )
    //
	//returns true on sucess or false on fail		
	function img_ttftext($string,$size,$angle,$x,$y,$color,$ttf_file,$ctype=""){
		$color=$this->img_getcolor($color,$ctype);
		@imagettftext($this->image,$size,$angle,$x,$y,$color,$ttf_file,$string);
	}
	
    //
    //public function img_fillbackground($color,$ctype="")
    //
    //Fills in the background of the image
    //INPUTS:
    //$color		-	rgb color numbers [array:attributes (r,g,b)]
    //$ctype		-	color type: close,default [string] (default: )
    //
	//returns true on sucess or false on fail		
	function img_fillbackground($color,$ctype=""){
		$this->img_drawrectangle($color,array(0,$this->width),array(0,$this->height),true,$ctype);
	}
	
    //
    //public function img_drawrectangle($color,$x,$y,$filled=true,$ctype="")
    //
    //Draws a string of text on the current image
    //INPUTS:
    //$color		-	rgb color numbers [array:attributes (r,g,b)]
    //$x			-	2 x positions [array]
    //$y			-	2 y positions [array]
    //$filled		-	rectangle is filled with color or not [boolean] (default: true)
    //$ctype		-	color type: close,default [string] (default: )
    //
	//returns true on sucess or false on fail			
	function img_drawrectangle($color,$x,$y,$filled=true,$ctype=""){
		$color=$this->img_getcolor($color,$ctype);
		if($filled){
			@imagefilledrectangle($this->image,$x[0],$y[0],$x[1],$y[1],$color);	
		}
		else{
			@imagerectangle($this->image,$x[0],$y[0],$x[1],$y[1],$color);	
		}
	}
	
	//
	//Image Manipulation Functions
	//	
	
    //
    //public function mg_presize($width_max,$height_max)
    //
    //Resizes the image use a proportional method.
    //INPUTS:
    //$width_max		-	maximum width for image [int]
    //$height_max		-	maximum heigh for image [int]
    //
	//returns true on sucess or false on fail			
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
	
    //
    //public function img_resize($new_width,$new_height)
    //
    //Resizes the image
    //INPUTS:
    //$new_width		-	new image width [int]
    //$new_height		-	new image height [int]
    //
	//returns true on sucess or false on fail		
	function img_resize($new_width,$new_height){
		if($this->type==IMAGETYPE_GIF){
			$tmp_img=@imagecreate($width,$height);
		}
		else{
			$tmp_img=@imagecreatetruecolor($width,$height);
		}
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
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	  
	
	
    //
    //private function img_gdversion()
    //
    //Gets the current GD version
    //
	//returns GD version		
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

    //
    //private function img_getcolor($color,$type="")
    //
    //Gets the color for an image given the rgb color value
    //INPUTS:
    //$color		-	rgb color numbers [array:attributes (r,g,b)]
    //$ctype		-	color type: close,default [string] (default: )
    //
	//returns color on sucess or false on fail		
	function img_getcolor($color,$type=""){
		if($type="close"){
			return @imagecolorclosest($this->image,$c["r"],$c["g"],$c["b"]);
		}
		else{
			return @imagecolorallocate($this->image,$c["r"],$c["g"],$c["b"]);
		}
	}
}