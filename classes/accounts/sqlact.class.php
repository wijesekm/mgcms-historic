<?php

/**
 * @file        sqlact.class.php
 * @author         Kevin Wijesekera
 * @copyright     2008
 * @edited        8-3-2008

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

class sqlact extends accounts{

    private $user;
    private $lastLength;
    private $table;

    const    GEN_PASSWORD_LENGTH = 12;

    public function __construct(){
        $this->lastLength=0;
        $this->table=(isset($GLOBALS['MG']['SITE']['ACCOUNT_TBL']))?array($GLOBALS['MG']['SITE']['ACCOUNT_TBL']):array(TABLE_PREFIX.'users');
    }

    final public function act_searchUsers($string,$ob='ASC'){
        return $this->act_search($string);
    }


    final public function act_search($string,$fields=false,$data=array(),$like=true){
        if($fields == false){
            $fields = array('user_uid');
        }
        $append='';
        if($like){
            $append='%';
        }
        $data = array_merge(array(array('user_uid'),array('user_fullname')),$data);
        $params = array();
        foreach($fields as $key=>$val){
            $params[]=array(DB_LIKE,array(DB_OR),$val,$append.$string.$append);
        }
        $params[count($params)-1][1] = false;
        $additParams=array();
        $additParams['orderby']=array(array('user_uid'),array('ASC'));
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
        }
        if(!$users=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,$data,$params,DB_ASSOC,DB_ALL_ROWS,$additParams)){
            trigger_error('(SQLACT): Could not load user data',E_USER_WARNING);
            return false;
        }
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
        }
        $ret = array();
        foreach($users as $key=>$val){
            if(!empty($val['user_uid'])){
                $ret[$val['user_uid']] = array();
                foreach($val as $key2=>$val2){
                    $key2 = strtoupper(substr($key2,strpos($key2,'_')+1));
                    $ret[$val['user_uid']][$key2]=$val2;
                }
            }
        }
        return $ret;
    }

    final public function act_load_query($query,$fields=array(array('user_email'))){
        $fields = array_merge(array(array('user_uid'),array('user_fullname')),$fields);
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
        }
        if(!$users=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,$fields,$query)){
            trigger_error('(SQLACT): Could not load user data',E_USER_WARNING);
            return false;
        }
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
        }
        $ret = array();
        foreach($users as $key=>$val){
            if(!empty($val['user_uid'])){
                $ret[$val['user_uid']] = array();
                foreach($val as $key2=>$val2){
                    $key2 = strtoupper(substr($key2,strpos($key2,'_')+1));
                    $ret[$val['user_uid']][$key2]=$val2;
                }
            }
        }
        return $ret;
    }

    final public function act_load($uid=false,$search=false,$start=false,$length=false,$acl=true,$ob='ASC',$fields=false){

        $this->user=array();

        $parms=array();
        $additParams=array();
        if($uid){
            $parms[]=array(false,false,'user_uid','=',$uid);
        }
        else if($search){
            $parms[]=array(DB_LIKE,array(DB_OR),'user_uid','%'.$search.'%');
            $parms[]=array(DB_LIKE,false,'user_fullname','%'.$search.'%');
        }
        else{
            $parms=false;
        }

        if($start!==false){
            $additParams['limit']=array($start,$length);
        }
        $additParams['orderby']=array(array('user_uid'),array($ob));

        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
        }
        if(!$users=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,$fields,$parms,DB_ASSOC,DB_ALL_ROWS,$additParams)){
            trigger_error('(SQLACT): Could not load user data',E_USER_WARNING);
            return false;
        }

        $this->lastLength=$GLOBALS['MG']['SQL']->sql_numRows($this->table,$parms);

        for($i=0;$i<$users['count'];$i++){
            $dta=$users[$i];
            if(is_array($dta)){
            $keys=array_keys($dta);
            $soq=count($dta);
            }
            else{
                $soq = 0;
            }

            for($j=0;$j<$soq;$j++){
                $nkey=strtoupper(substr($keys[$j],strpos($keys[$j],'_')+1));
                switch($nkey){
                    case 'IM':
                        $tmp=explode(';',$dta[$keys[$j]]);
                        $sot=count($tmp);
                        for($k=0;$k<$sot;$k++){
                            $curim=explode(':',$tmp[$k]);
                            if(isset($curim[1])){
                                $this->user[$dta['user_uid']]['IM'][$curim[0]]=$curim[1];
                            }
                        }
                    break;
                    case 'BANNED':
                        $this->user[$dta['user_uid']]['BANNED']=(boolean)$dta[$keys[$j]];
                    break;
                    case 'NAME':
                    case 'FULLNAME':
                        $this->user[$dta['user_uid']]['NAME']=explode(';',$dta[$keys[$j]]);
                    break;
                    default:
                        $this->user[$dta['user_uid']][$nkey]=$dta[$keys[$j]];
                    break;
                }
            }
        }
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
        }
        for($i=0;$i<$users['count'];$i++){
            if($acl){
                $gp = new group();
                $this->user[$users[$i]['user_uid']]['GROUPS']=$gp->group_getMembership($users[$i]['user_uid']);
                $ac = new acl();
                $this->user[$users[$i]['user_uid']]['ACL']=$ac->acl_load($users[$i]['user_uid'],$this->user[$users[$i]['user_uid']]['GROUPS']);
            }
        }
        return $this->user;
    }

    final public function act_isAccount($uid){
        $c=false;
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
        }
        if($GLOBALS['MG']['SQL']->sql_fetchResult($this->table,array(array('user_uid')),array(array(false,false,'user_uid','=',$uid)))){
            $c=true;
        }
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
        }
        return $c;
    }

    final public function act_getLastLength(){
        return $this->lastLength;
    }

    final public function act_remove($uid){
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
        }
        if(!$uid){
            return false;
        }
        $params=array(array(false,false,'user_uid','=',$uid));
        if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,$params)){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
            return false;
        }
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
        }
        return true;
    }


    final public function act_updateField($uid,$field,$value){
        if(!$uid){
            return false;
        }
        $up=array(array($field,$value));
        $up[]=array('user_account_modified',$GLOBALS['MG']['SITE']['TIME']);
        $up[]=array('user_modifiedBy',$GLOBALS['MG']['USER']['UID']);
        $params=array(array(false,false,'user_uid','=',$uid));
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
        }
        $r=$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$this->table,$params,$up);
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
        }
        return $r;
    }

    final public function act_update($uid,$name,$email,$rview,$auth,$lang,$banned,$tz,$tf,$other=false){
        if(!$uid){
            return false;
        }
        if(!is_array($name)){
            $name=explode(' ',$name);
        }
        if(!is_array($rview)){
            $rview=array($rview);
        }
        $params=array(array(false,false,'user_uid','=',$uid));
        if(!empty($name)){
            $up=array(array('user_fullname',implode(';',$name)));
        }
        if(!empty($email)){
            $up[]=array('user_email',$email);
        }

        $up[]=array('user_restrictView',implode(';',$rview).';');
        if(!empty($auth)){
            $up[]=array('user_auth',$auth);
        }
        if(!empty($lang)){
            $up[]=array('user_lang',$lang);
        }
        $up[]=array('user_banned',($banned==true)?'1':'0');
        if(!empty($tz)){
            $up[]=array('user_tz',$tz);
        }
        if(!empty($tf)){
            $up[]=array('user_twofactor',$tf);
        }

        $up[]=array('user_account_modified',$GLOBALS['MG']['SITE']['TIME']);
        $up[]=array('user_modifiedBy',$GLOBALS['MG']['USER']['UID']);
        if($other!=false){
            foreach($other as $key=>$val){
                $up[]=array($key,$val);
            }
        }
        $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
        $r=$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$this->table,$params,$up);
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
        }
        return $r;
    }

    final public function act_add($uid,$name,$email,$rview,$auth,$lang,$tz,$tf,$other=false){
        if(!$uid){
            return false;
        }

        $params=array('user_uid','user_email','user_auth','user_account_created','user_account_modified','user_addedBy');
        $data=array($uid,$email,$auth,$GLOBALS['MG']['SITE']['TIME'],$GLOBALS['MG']['SITE']['TIME'],$GLOBALS['MG']['USER']['UID']);

        if($auth=='sqlauth'){
            $params[]='user_pass_expired';
            $data[]=$GLOBALS['MG']['SITE']['TIME'];
        }

        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
        }

        if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,$this->table,$params,$data)){
            if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
                $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
            }
            return false;
        }
        if(isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
            $GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
        }

        //update account
        if(!$this->act_update($uid,$name,$email,$rview,false,$lang,false,$tz,$tf,$other)){
            return false;
        }
        $newPass = true;
        if($auth=='sqlauth'){
            mginit_loadPackage(array(array('auth','abstract','/classes/auth/'),array('sqlauth','class','/classes/auth/')));
            $auth=new sqlauth();
            $newPass=$auth->auth_randstring(sqlact::GEN_PASSWORD_LENGTH);
            if(!$auth->auth_changePass($uid,$newPass,$encoding)){
                return false;
            }
        }
        return $newPass;
    }
}