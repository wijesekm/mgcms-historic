<?php
/**
 * This file provides the main initialization interface for the CMS
 *
 * @package OMNI CMS\Secrets
 *
 * @author Kevin Wijesekera <kwijesekera@munciepower.com>
 * @copyright 2026 Muncie Power Products
 *
 */
if(!defined('STARTED')){
    die();
}

class secrets {

    private $secret_cache;

    public function __construct(){
        if(is_file($GLOBALS['MG']['CFG']['PATH']['SECRETS'].'/.secrets')){
            $raw = file_get_contents($GLOBALS['MG']['CFG']['PATH']['SECRETS'].'/.secrets');
            $raw = explode("\n",$raw);
            foreach($raw as $v){
                $v = explode('=',$v);
                if(count($v) == 2){
                    $k = trim($v[0]);
                    $v = trim($v[1]);
                    if(strpos($v,',')){
                        $v = explode(',',$v);
                    }
                    $this->secret_cache[$k] = $v;
                }
            }
        }
    }

    public function get($key){
        if(!empty($this->secret_cache[$key])){
            return $this->secret_cache[$key];
        }
        return "";
    }

    public function getCertPath($key,$public=true){
        $type = ($public)?'.crt':'.key';
        if(is_file($GLOBALS['MG']['CFG']['PATH']['SECRETS'].$key.$type)){
            return $GLOBALS['MG']['CFG']['PATH']['SECRETS'].$key.$type;
        }
        return false;
    }

    public function getCert($key,$public=true){
        $p = $this->getCertPath($key,$public);
        if($p){
            return file_get_contents($p);
        }
        return false;
    }
}