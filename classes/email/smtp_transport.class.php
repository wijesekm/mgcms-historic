<?php

class smtp_transport extends mail_transport{

    private $obj;


    public function __destruct()
    {
        //Close any open SMTP connection nicely
        $this->smtpClose();
    }

    public function send($envelope){
        if(!$this->smtpConnect()) {
            return false;
        }

        if (!$this->obj->mail($envelope->sender)) {
            trigger_error('(smtp_transport): Could not set sender',E_USER_WARNING);
            return false;
        }

        // Attempt to send to all recipients
        $can_send = false;
        foreach ([$envelope->to, $envelope->cc, $envelope->bcc] as $togroup) {
            foreach ($togroup as $to) {
                if ($this->obj->recipient($to[0])) {
                    $can_send = true;
                }
                else{
                    trigger_error('(smtp_transport): Bad recipient: '.$to[0],E_USER_NOTICE);
                }
            }
        }

        // Only send the DATA command if we have viable recipients
        if (!$can_send) {
            trigger_error('(smtp_transport): Message must have oen valid recipient',E_USER_WARNING);
            return false;
        }

        if(!$this->obj->data($envelope->header . $envelope->body)){
            trigger_error('(smtp_transport): Could not send message',E_USER_WARNING);
            return false;
        }

        //$smtp_transaction_id = $this->obj->getLastTransactionID();

        if ($this->config['keepalive']) {
            $this->obj->reset();
        }
        else {
            $this->obj->quit();
            $this->obj->close();
        }

        return true;
    }

    private function smtpConnect(){
        $this->obj = new SMTP();

        // Already connected?
        if($this->obj->connected()){
            return true;
        }

        $this->obj->setTimeout($this->config['timeout']);
        $this->obj->setDebugLevel($this->config['debug']);
        $this->obj->setDebugOutput($this->config['debug_output']);
        $this->obj->setVerp(!empty($this->config['verp']));
        $hosts = explode(';', $this->config['hosts']);
        foreach($hosts as $hostentry){
            $hostinfo = [];
            if(!preg_match('/^((ssl|tls):\/\/)*([a-zA-Z0-9\.-]*|\[[a-fA-F0-9:]+\]):?([0-9]*)$/', trim($hostentry),
                $hostinfo)){
                trigger_error('(smtp_transport): Invalid host: ' . $hostentry, E_USER_NOTICE);
                // Not a valid host entry
                continue;
            }
            // $hostinfo[2]: optional ssl or tls prefix
            // $hostinfo[3]: the hostname
            // $hostinfo[4]: optional port number
            // The host string prefix can temporarily override the current setting for SMTPSecure
            // If it's not specified, the default value is used

            // Check the host name is a valid name or IP address before trying to use it
            if(!$this->isValidHost($hostinfo[3])){
                trigger_error('(smtp_transport): Invalid host: ' . $hostentry, E_USER_NOTICE);
                continue;
            }
            $prefix = '';
            $secure = $this->config['secure'];
            $tls = ('tls' == $secure);
            if('ssl' == $hostinfo[2] or ('' == $hostinfo[2] and 'ssl' == $secure)){
                $prefix = 'ssl://';
                $tls = false; // Can't have SSL and TLS at the same time
                $secure = 'ssl';
            }
            elseif('tls' == $hostinfo[2]){
                $tls = true;
                // tls doesn't use a prefix
                $secure = 'tls';
            }
            // Do we need the OpenSSL extension?
            $sslext = defined('OPENSSL_ALGO_SHA256');
            if('tls' === $secure or 'ssl' === $secure){
                // Check for an OpenSSL constant rather than using extension_loaded, which is sometimes disabled
                if(!$sslext){
                    trigger_error('(smtp_transport): Open SSL Required for smtp', E_USER_ERROR);
                    return false;
                }
            }
            $host = $hostinfo[3];
            $port = $this->config['port'];
            $tport = (int) $hostinfo[4];
            if($tport > 0 and $tport < 65536){
                $port = $tport;
            }
            if($this->obj->connect($prefix . $host, $port, $this->config['timeout'], $this->config['options'])){
                try{
                    if(!empty($this->config['hello'])){
                        $hello = $this->config['hello'];
                    }
                    else{
                        $hello = $this->config['hostname'];
                    }
                    $this->obj->hello($hello);
                    // Automatically enable TLS encryption if:
                    // * it's not disabled
                    // * we have openssl extension
                    // * we are not already using SSL
                    // * the server offers STARTTLS
                    if($this->config['auto_tls'] and $sslext and 'ssl' != $secure and $this->obj->getServerExt(
                        'STARTTLS')){
                        $tls = true;
                    }
                    if($tls){
                        if(!$this->obj->startTLS()){
                            trigger_error('(smtp_transport): Could not start TLS', E_USER_WARNING);
                            return false;
                        }
                        // We must resend EHLO after TLS negotiation
                        $this->obj->hello($hello);
                    }
                    if($this->config['username']){
                        if(!$this->obj->authenticate($this->config['username'], $this->config['password'],
                            $this->config['auth_type'])){
                            trigger_error('(smtp_transport): Could not authenticate with server', E_USER_WARNING);
                            return false;
                        }
                    }
                    return true;
                }
                catch(Exception $exc){
                    trigger_error('(smtp_transport): Exception occured: '.$exc, E_USER_WARNING);
                    // We must have connected, but then failed TLS or Auth, so close connection nicely
                    $this->obj->quit();
                }
            }
        }
        // If we get here, all connection attempts have failed, so close connection hard
        $this->obj->close();
        trigger_error('(smtp_transport): No host to connect to', E_USER_WARNING);
        return false;
    }

    private function smtpClose(){
        if ($this->obj !== null) {
            if ($this->obj->connected()) {
                $this->obj->quit();
                $this->obj->close();
            }
        }
    }

    private function isValidHost($host)
    {
        //Simple syntax limits
        if (empty($host)
            or !is_string($host)
            or strlen($host) > 256
            ) {
                return false;
            }
            //Looks like a bracketed IPv6 address
            if (trim($host, '[]') != $host) {
                return (bool) filter_var(trim($host, '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
            }
            //If removing all the dots results in a numeric string, it must be an IPv4 address.
            //Need to check this first because otherwise things like `999.0.0.0` are considered valid host names
            if (is_numeric(str_replace('.', '', $host))) {
                //Is it a valid IPv4 address?
                return (bool) filter_var($host, FILTER_VALIDATE_IP);
            }
            if (filter_var('http://' . $host, FILTER_VALIDATE_URL)) {
                //Is it a syntactically valid hostname?
                return true;
            }

            return false;
    }

}