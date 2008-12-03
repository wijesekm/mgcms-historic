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
	
	const IMG_MIME_GIF	=	'image/gif';
	const IMG_MIME_PNG	=	'image/png';
	const IMG_MIME_JPEG	=	'image/jpeg';
	const IMG_EXT_GIF	=	'.gif';
	const IMG_EXT_PNG	=	'.png';
	const IMG_EXT_JPEG	=	'.jpg';
	
	public function img_getDetails(){
		return array($this->width,$this->height,$this->mime,$this->ext);
	}
	
	public function img_createImg($mime,$width,$height,$truecolor=true){
		$this->mime=$mime;
		$this->width=$width;
		$this->height=$height;
		switch($this->mime){
			case mgimg::IMG_MIME_GIF:
				$this->ext=mgimg::IMG_EXT_GIF;
			break;
			case mgimg::IMG_MIME_PNG:
				$this->ext=mgimg::IMG_EXT_PNG;
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
			trigger_error('(MGIMG): Could not create new image. ',E_USER_ERROR);
			return false;
		}
		return true;
	}
	
	public function img_loadImg($file){
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
		return true;
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
		$this->img_createImg($this->mime,$this->width,$this->height,true);
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
			$f=$filename.$this->ext;
		}
		switch($this->mime){
			case mgimg::IMG_MIME_GIF:
				if(!imagegif($this->img,$f)){
					trigger_error('(MGIMG): Could not save image!',E_USER_ERROR);
					return false;
				}
			break;
			case mgimg::IMG_MIME_PNG:
				if(!imagepng($this->img,$f,$quality,$pngfilters)){
					trigger_error('(MGIMG): Could not save image!',E_USER_ERROR);
					return false;
				}
			break;
			case mgimg::IMG_MIME_JPEG:
				if(!imagejpeg($this->img,$f,$quality)){
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
			return $content;
		}
		return true;
	}
	
	public function img_close(){
		imagedestroy($this->img);
	}
	
	private function img_setHexColor($color){
		list($r,$g,$b)=array(hexdec(substr($color,0,2)),hexdec(substr($color,2,2)),hexdec(substr($color,4,2)));
		return $this->img_setRGBColor($r,$g,$b);
	}
	private function img_setRGBColor($r,$g,$b){
		return imagecolorallocate($this->img,$r,$g,$b);	
	}

}