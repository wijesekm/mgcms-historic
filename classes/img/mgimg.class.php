<?php

/**
 * @file		mgimg.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		11-24-2008
 
 ###################################
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see http://www.gnu.org/licenses/.
 ###################################
 */

if(!defined('STARTED')){
	die();
}

class mgimg{
	
	private $mime;
	private $ext;
	private $img;
	private $height;
	private $width;
	private $tc;
	private $font;
	
	const IMG_MIME_GIF	=	'image/gif';
	const IMG_MIME_PNG	=	'image/png';
	const IMG_MIME_JPEG	=	'image/jpeg';
	const IMG_EXT_GIF	=	'.gif';
	const IMG_EXT_PNG	=	'.png';
	const IMG_EXT_JPEG	=	'.jpg';
	
	public function img_getDetails(){
		return array($this->width,$this->height,$this->mime,$this->ext);
	}
	
	public function img_create($mime,$width,$height,$truecolor=true){
		$this->img=false;
		$this->mime=$mime;
		$this->width=$width;
		$this->height=$height;
		switch($this->mime){
			case mgimg::IMG_MIME_GIF:
				$this->ext=mgimg::IMG_EXT_GIF;
			break;
			case mgimg::IMG_MIME_PNG:
				$this->ext=mgimg::IMG_EXT_PNG;
				$this->tc=true;
			break;
			case mgimg::IMG_MIME_JPEG:;
				$this->ext=mgimg::IMG_EXT_JPEG;
			break;
			default:
				trigger_error('(MGIMG): Unsupported image type: new image - '.$this->mime,E_USER_ERROR);
				return false;
			break;		
		};
		if($truecolor&&$this->mime!=mgimg::IMG_MIME_GIF){
			$this->img=imagecreatetruecolor($width,$height);	
		}
		else{
			$this->img=imagecreate($width,$height);			
		}
		if(!$this->img){
			//trigger_error('(MGIMG): Could not create new image. ',E_USER_ERROR);
			//return false;
		}
		return true;
	}
	
	public function img_load($file,$return=false){
		if($return){
		 	$t = getimagesize($file);
		 	$ret=array();
		 	$ret[1]=$t[0];
		 	$ret[2]=$t[1];
		 	$ret[3]=$t['mime'];
			switch($ret[3]){
				case mgimg::IMG_MIME_GIF:
					$ret[0]=imagecreatefromgif($file);
					$ret[5]=mgimg::IMG_EXT_GIF;
				break;
				case mgimg::IMG_MIME_PNG:
					$ret[0]=imagecreatefrompng($file);
					$ret[5]=mgimg::IMG_EXT_PNG;
				break;
				case mgimg::IMG_MIME_JPEG:
					$ret[0]=imagecreatefromjpeg($file);
					$ret[5]=mgimg::IMG_EXT_JPEG;
				break;
				default:
					trigger_error('(MGIMG): Unsupported image type: '.$file.' - '.$ret[3],E_USER_ERROR);
					return false;
				break;
			};
			if(!$ret[0]){
				trigger_error('(MGIMG): Could not create image from file: '.$file,E_USER_ERROR);
				return false;
			}
			return $ret;			
		}
		else{
		 	$t = getimagesize($file);
		 	$this->width=$t[0];
		 	$this->height=$t[1];
		 	$this->mime=$t['mime'];
			switch($this->mime){
				case mgimg::IMG_MIME_GIF:
					$this->img=imagecreatefromgif($file);
					$this->ext=mgimg::IMG_EXT_GIF;
				break;
				case mgimg::IMG_MIME_PNG:
					$this->img=imagecreatefrompng($file);
					$this->ext=mgimg::IMG_EXT_PNG;
				break;
				case mgimg::IMG_MIME_JPEG:
					$this->img=imagecreatefromjpeg($file);
					$this->ext=mgimg::IMG_EXT_JPEG;
				break;
				default:
					trigger_error('(MGIMG): Unsupported image type: '.$file.' - '.$this->mime,E_USER_ERROR);
					return false;
				break;
			};
			if(!$this->img){
				trigger_error('(MGIMG): Could not create image from file: '.$file,E_USER_ERROR);
				return false;
			}			
		}
		return true;
	}
	
	public function img_setBackground($fillColor,$borderColor=false,$borderWidth=1){
		if(!$borderColor){
			$borderColor=$fillColor;
		}
		if(!$this->img_drawRectangle(0,$this->width,0,$this->height,$borderColor,$borderThickness,$fillColor)){
			return false;
		}
	}
	
	public function img_drawRectangleBorder($x1,$x2,$y1,$y2,$borderColor,$borderWidth){
		list($c,$a)=$borderColor;
		$color=$this->img_setHexColor($c,$a);
		for($i=0;$i<$borderWidth;$i++){
			$xstart=$x1+$i;
			$xend=$x2-$i;
			$ystart=$y1+$i;
			$yend=$y2-$i;
			if(!imagerectangle($this->img,$xstart,$ystart,$xend,$yend,$color)){
				trigger_error('(MGIMG): Could not draw rectangle (imagerectangle)',E_USER_ERROR);
				return false;
			}
		}
		return true;
	}
	
	public function img_drawRectangle($x1,$x2,$y1,$y2,$borderColor,$borderThickness,$fillColor){
		$x1_inner=$x1+$borderThickness;
		$y1_inner=$y1+$borderThickness;
		$x2_inner=$x2-$borderThickness;
		$y2_inner=$y2-$borderThickness;
		list($c,$a)=$fillColor;
		if(!imagefilledrectangle($this->img,$x1_inner,$y1_inner,$x2_inner,$y2_inner,$this->img_setHexColor($c,$a))){
			trigger_error('(MGIMG): Could not draw filled rectangle (imagefilledrectangle)',E_USER_ERROR);
			return false;
		}
		if(!$this->img_drawRectangleBorder($x1,$x2,$y1,$y2,$borderColor,$borderThickness)){
			return false;
		}
		return true;
	}
	
	public function img_drawImageTransparent($img_path,$sx,$sy,$sw,$sh,$dx,$dy){
		list($insert,$x,$y,$mime,$ext)=$this->img_load($img_path,true);
		if($mime!=mgimg::IMG_MIME_PNG){
			trigger_error('(MGIMG): Cannot draw transparent image with GIFs or JPGs',E_USER_ERROR);
			return false;
		}
		if($this->mime==mgimg::IMG_MIME_GIF){
			$temp=$this->img;
			$this->img_create($this->mime,$this->width,$this->height,true);
			imagecopy($this->img,$temp,0,0,0,0,$this->width,$this->height);
			imagedestroy($temp);
		}
	    imageAlphaBlending($insert, false);
	    imageSaveAlpha($insert, true);
		if(!imagecopy($this->img,$insert,$dx,$dy,$sx,$sy,$sw,$sh)){
			trigger_error('(MGIMG): Could not merge images for drawImage',E_USER_ERROR);
			return false;
		}
		imagedestroy($insert);
		return true;
	}
	
	public function img_drawArcBorder($cx,$cy,$width,$height,$startAng,$endAng,$borderColor,$borderWidth){
		list($c,$a)=$borderColor;
		$color=$this->img_setHexColor($c,$a);
		for($i=0;$i<$borderWidth;$i++){
			$nwidth=$width-$i;
			$nheight=$height-$i;
			if(!imagearc($this->img,$cx,$cy,$nwidth,$nheight,$startAng,$endAng,$color)){
				trigger_error('(MGIMG): Could not draw rectangle (imagerectangle)',E_USER_ERROR);
				return false;
			}
		}
		return true;
	}
	
	public function img_drawString($string,$x,$y,$color,$up=false){
		list($c,$a)=$color;
		if($up){
			if(!imagestringup($this->img,$this->font,$x,$y,$string,$this->img_setHexColor($c,$a))){
				trigger_error('(MGIMG): Could not draw string',E_USER_ERROR);
				return false;
			}
		}
		else{
			if(!imagestring($this->img,$this->font,$x,$y,$string,$this->img_setHexColor($c,$a))){
				trigger_error('(MGIMG): Could not draw string',E_USER_ERROR);
				return false;
			}			
		}
		return true;
	}
	
	public function img_loadFont($font){
		if(($font > 0 && $font < 6)){
			$this->font=$font;
		}
		else{
			if(!$f=imageloadfont($font_file)){
				trigger_error('(MGIMG): Could not load font file.  Will set default font to standard font.',E_USER_WARNING);
				$this->font=$font;
			}
			else{
				$this->font=$f;
			}		
		}	
		return true;
	}
	
	public function img_resizeMax($maxWidth,$maxHeight){
		$a=$this->width-$maxWidth;
		$b=$this->height-$maxHeight;
		if($a > 0 && $b > 0){
			if($a > $b){
				$newWidth=$maxWidth;
				$newHeight=$newWidth/$this->width * $this->height;
			}
			else{
				$newHeight=$maxHeight;
				$newWidth=$newHeight/$this->height * $this->width;
			}
		}
		else if ($a > 0){
			$newWidth=$maxWidth;
			$newHeight=$newWidth/$this->width * $this->height;
		}
		else if ($b > 0){
			$newHeight=$maxHeight;
			$newWidth=$newHeight/$this->height * $this->width;
		}
		else{
			return false;
		}
		return $this->img_resize($newWidth,$newHeight);
	}
	
	
	public function img_resize($w,$h,$type='percent'){
	 	$oldwidth=$this->width;
	 	$oldheight=$this->height;
		if($type=='percent'){
			$this->width=round($this->width*$w);
			$this->height=round($this->height*$h);
		}
		else{
			$this->width=$w;
			$this->height=$h;
		}
		$orig=$this->img;
		$this->img_create($this->mime,$w,$h,true);
		if(!imagecopyresampled($this->img,$orig,0,0,0,0,$this->width,$this->height,$oldwidth,$oldheight)){
			imagedestroy($orig);
			return false;
		}
		imagedestroy($orig);
		return true;
	}
	
	public function img_display($filename=false,$jpgquality=100,$pngcomp=9,$pngfilters=false){
	 	$f=$GLOBALS['MG']['CFG']['PATH']['TMP'].'/'.md5(uniqid(rand(),true));
		if($filename){
			$f=$filename;
		}
		switch($this->mime){
			case mgimg::IMG_MIME_GIF:
				if(!imagegif($this->img,$f)){
					trigger_error('(MGIMG): Could not save image!',E_USER_ERROR);
					return false;
				}
			break;
			case mgimg::IMG_MIME_PNG:
				if(!imagepng($this->img,$f,$pngcomp,$pngfilters)){
					trigger_error('(MGIMG): Could not save image!',E_USER_ERROR);
					return false;
				}
			break;
			case mgimg::IMG_MIME_JPEG:
				if(!imagejpeg($this->img,$f,$jpgquality)){
					trigger_error('(MGIMG): Could not save image!',E_USER_ERROR);
					return false;
				}
			break;			
		};
		if(!$filename){
			$r=fopen($f,'r');
			$content='';
			while(!feof($r)){
				$content.=fgets($r);
			}
			fclose($r);
			unlink($f);
			$GLOBALS['MG']['LANG']['CONTENT_TYPE']=$this->mime;
			$GLOBALS['MG']['PAGE']['NOSITETPL']=true;
			return $content;
		}
		return true;
	}
	
	public function img_close(){
		imagedestroy($this->img);
	}
	
	private function img_setHexColor($color,$alpha=0){
		list($r,$g,$b)=array(hexdec(substr($color,0,2)),hexdec(substr($color,2,2)),hexdec(substr($color,4,2)));
		return $this->img_setRGBColor($r,$g,$b,$alpha);
	}
	private function img_setRGBColor($r,$g,$b,$a){
		if($this->tc&&$a>0){
			return imagecolorallocatealpha($this->img,$r,$g,$b,$a);
		}
		return imagecolorallocate($this->img,$r,$g,$b);	
	}

}