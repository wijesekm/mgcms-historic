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

	function form_validator(&$sql,$config){
		$this->db=$sql;
		//$this->config=$config;
		$this->config=array("chars"=>4,
							"websafecolors"=>false,
							"noise"=>4
							)
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
	function fv_makecaptcha($app,$id){
		$private_key = $this->generate_private();
			
		// create Image and set the apropriate function depending on GD-Version & websafecolor-value
		if($this->fv_gdversion() >= 2 && !$this->config["websafecolors"]){
			$func1 = 'imagecreatetruecolor';
			$func2 = 'imagecolorallocate';
		}
		else{
			$func1 = 'imageCreate';
			$func2 = 'imagecolorclosest';
		}
		$image = $func1($this->config["x"],$this->config["y"]);

		// Set Backgroundcolor
		$colors=$this->fv_randomcolor(224, 255);
		$back =  @imagecolorallocate($image, $colors["r"], $colors["g"], $colors["b"]);
		@ImageFilledRectangle($image,0,0,$this->config["x"],$this->config["y"],$back);

		// allocates the 216 websafe color palette to the image
		if($this->fv_gdversion() < 2 || $this->config["websafecolors"]) $this->makeWebsafeColors($image);
		if($this->config["noise"] > 0){
			// random characters in background with random position, angle, color
			for($i=0; $i < $this->config["noise"]; $i++){
				srand((double)microtime()*1000000);
				$size	= intval(rand((int)($this->minsize / 2.3), (int)($this->maxsize / 1.7)));
				srand((double)microtime()*1000000);
				$angle	= intval(rand(0, 360));
				srand((double)microtime()*1000000);
				$x=intval(rand(0, $this->config["x"]));
				srand((double)microtime()*1000000);
				$y=intval(rand(0, (int)($this->config["y"] - ($size / 5))));
				$colors=$this->fv_randomcolor(160, 224);
				$color=$func2($image, $colors["r"], $colors["g"], $colors["b"]);
				srand((double)microtime()*1000000);
				$text=chr(intval(rand(45,250)));
				@ImageTTFText($image, $size, $angle, $x, $y, $color, $this->change_TTF(), $text);
			}
		}
		else{
			for($i=0; $i < $this->lx; $i += (int)($this->minsize / 1.5)){
				$this->random_color(160, 224);
				$color=$func2($image, $this->r, $this->g, $this->b);
				@imageline($image, $i, 0, $i, $this->ly, $color);
			}
			for($i=0 ; $i < $this->ly; $i += (int)($this->minsize / 1.8)){
				$this->random_color(160, 224);
				$color=$func2($image, $this->r, $this->g, $this->b);
				@imageline($image, 0, $i, $this->lx, $i, $color);
			}
		}
		for($i=0, $x = intval(rand($this->minsize,$this->maxsize)); $i < $this->chars; $i++){
			$text=strtoupper(substr($private_key, $i, 1));
			srand((double)microtime()*1000000);
			$angle=intval(rand(($this->maxrotation * -1), $this->maxrotation));
			srand((double)microtime()*1000000);
			$size=intval(rand($this->minsize, $this->maxsize));
			srand((double)microtime()*1000000);
			$y=intval(rand((int)($size * 1.5), (int)($this->ly - ($size / 7))));
			$this->random_color(0, 127);
			$color=$func2($image, $this->r, $this->g, $this->b);
			$this->random_color(0, 127);
			$shadow=$func2($image, $this->r + 127, $this->g + 127, $this->b + 127);
			@ImageTTFText($image, $size, $angle, $x + (int)($size / 15), $y, $shadow, $this->change_TTF(), $text);
			@ImageTTFText($image, $size, $angle, $x, $y - (int)($size / 15), $color, $this->TTF_file, $text);
			$x += (int)($size + ($this->minsize / 5));
		}
		@ImageJPEG($image, $GLOBALS["MANDRIGO_CONFIG"]["IMG_PATH"].TMP_IMG."/".$app."_".$id.".jpg", $this->jpegquality);
		@ImageDestroy($image);
		if(!$this->db->db_update(DB_INSERT,TABLE_PREFIX.TABLE_TEMP,array("$app.$id",$rnd))){
			return false;
		}
		return true;
	}
	function fv_randomcolor($min,$max){
	  	$colors=array();
		srand((double)microtime() * 1000000);
		$colors["r"] = intval(rand($min,$max));
		srand((double)microtime() * 1000000);
		$colors["g"] = intval(rand($min,$max));
		srand((double)microtime() * 1000000);
		$colors["b"] = intval(rand($min,$max));
		return $colors;
		//echo " (".$this->r."-".$this->g."-".$this->b.") ";
	}
	function makeWebsafeColors(&$image){
		//$a = array();
		for($r = 0; $r <= 255; $r += 51){
			for($g = 0; $g <= 255; $g += 51){
				for($b = 0; $b <= 255; $b += 51){
					$color = imagecolorallocate($image, $r, $g, $b);
					//$a[$color] = array('r'=>$r,'g'=>$g,'b'=>$b);
				}
			}
		}
	}
	function fv_generatekey($public,$key){
		$key = substr(md5($key.$public), 16 - $this->config["chars"] / 2, $this->config["chars"]);
		return $key;
	}
	function fv_gdversion(){
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