<?php
/**
 * This file provides a class for encrypting/decrypting
 * data
 *
 * @package OMNI CMS\Libraries
 *
 * @author Kevin Wijesekera <kwijesekera@munciepower.com>
 * @copyright 2021 Muncie Power Products
 * @license GPL
 *
 */
if(!defined('STARTED')){
    die();
}

define('CRYPT_TYPE_CERT', 0);
define('CRYPT_TYPE_AES256', 1);

/**
 * CMS Crypto Client class.  This class provides an interface for
 * encrypting/decrypting data
 *
 * @author Kevin Wijesekera <kwijesekera@munciepower.com>
 *
 */
class crypto{

    /** @var string Key to use for crypto requests */
    private $key;
    /** @var int Type of crypto (CRYPT_TYPE_CERT, CRYPT_TYPE_AES256) */
    private $type;
    /** @var array[] Internal use only data */
    private $intData;

    /**
     * This is the class constructor which sets up variables and checks to see
     * that the openssl library is loaded
     *
     */
    public function __construct(){
        $this->key = false;
        $this->type = false;
        $this->intData = array();
        if(!function_exists('openssl_pkey_get_private')){
            trigger_error('(crypt): openssl library not loaded',E_USER_ERROR);
        }
    }

    /**
     * This function sets up a crypto session
     *
     * @param int $type Type of crypto (CRYPT_TYPE_CERT, CRYPT_TYPE_AES256)
     * @param string $key Certificate or Key to use for crypto session
     * @param array[] $opts Other options to use for session which may contain:
     *                  .priv True if key is private certificate
     *                  .pass Password to unlock certificate if required
     *
     * @return boolean Status of setup
     */
    public function c_setup($type, $key, $opts=array()){
        $this->key = false;
        $this->type = $type;
        switch($type){
            case CRYPT_TYPE_CERT:
                if(!empty($opts['priv'])){
                    $this->intData['priv'] = true;
                    if(strpos($key,'-----') === false){
                        $key = chunk_split($key,64,"\n");
                        $key = "-----BEGIN RSA PRIVATE KEY-----\n".$key."-----END RSA PRIVATE KEY-----\n";
                    }
                    $pass = (isset($opts['pass']))?$opts['pass']:false;
                    $this->key = openssl_pkey_get_private($key,$pass);
                }
                else{
                    $this->intData['priv'] = false;
                    if(strpos($key,'-----') === false){
                        $key = chunk_split($key,64,"\n");
                        $key = "-----BEGIN PUBLIC KEY-----\n".$key."-----END PUBLIC KEY-----\n";
                    }
                    $this->key = openssl_pkey_get_public($key);
                }
            break;
            case CRYPT_TYPE_AES256:
                $this->key = $key;
                $this->intData['ivlen'] = openssl_cipher_iv_length('AES-256-CBC');
            break;
            default:
                trigger_error('(crypt): Unsupported crypt type',E_USER_ERROR);
            return false;
        };

        if(!$this->key){
            trigger_error('(crypto): Could not load key',E_USER_WARNING);
            return false;
        }
        return true;
    }

    /**
     * This function encrypts plaintext data
     *
     * @param string $text Data to encrypt
     *
     * @return string|boolean Base64 encoded encrypted data or false on error
     */
    public function c_encrypt($text){
        if(!$this->key){
            return false;
        }
        $crypt = false;

        switch($this->type){
            case CRYPT_TYPE_CERT:
                if($this->intData['priv']){
                    if(!openssl_private_encrypt($text,$crypt,$this->key)){
                        trigger_error('(crypto): Could not encrypt data',E_USER_WARNING);
                        return false;
                    }
                }
                else{
                    if(!openssl_public_encrypt($text,$crypt,$this->key)){
                        trigger_error('(crypto): Could not encrypt data',E_USER_WARNING);
                        return false;
                    }
                }
            break;
            case CRYPT_TYPE_AES256:
                $iv = openssl_random_pseudo_bytes($this->intData['ivlen']);

                $raw = openssl_encrypt($text, "AES-256-CBC", $this->key, OPENSSL_RAW_DATA, $iv);
                $hmac = hash_hmac('sha256', $raw, $this->key, true);
                $crypt = $iv.$hmac.$raw;
            break;
            default:
                trigger_error('(crypt): Unsupported crypt type',E_USER_ERROR);
            return false;
        };

        return base64_encode($crypt);
    }

    /**
     * This function decrypts base64 encoded data
     *
     * @param string $text Base64 encoded data to decrypt
     *
     * @return string|boolean Plaintext data decrypted or false on error
     */
    public function c_decrypt($crypt){
        if(!$this->key){
            return false;
        }
        $text = false;

        $crypt = base64_decode($crypt);

        switch($this->type){
            case CRYPT_TYPE_CERT:
                if($this->intData['priv']){
                    if(!openssl_private_decrypt($crypt,$text,$this->key)){
                        trigger_error('(crypto): Could not decrypt data',E_USER_WARNING);
                        return false;
                    }
                }
                else{
                    if(!openssl_public_decrypt($crypt,$text,$this->key)){
                        trigger_error('(crypto): Could not decrypt data',E_USER_WARNING);
                        return false;
                    }
                }
            break;
            case CRYPT_TYPE_AES256:
                $iv = substr($crypt, 0, $this->intData['ivlen']);
                $hmac = substr($crypt, $this->intData['ivlen'], 32);
                $raw = substr($crypt, $this->intData['ivlen']+32);
                $text = openssl_decrypt($raw, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
                $calcmac = hash_hmac('sha256', $raw, $this->key, true);
                if($hmac != $calcmac){
                    return false;
                }
            break;
            default:
                trigger_error('(crypt): Unsupported crypt type',E_USER_ERROR);
            return false;
        };

        return $text;
    }

    /**
     * This function generates a random secure alphanumeric string
     *
     * @param int $length Length of string to generate
     *
     * @return string Random generated string
     */
    public function c_rand($length){
        $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        for ($i=0; $i< $length; $i++){
            $str .= $char[random_int(1,61)];
        }
        return $str;
    }

}