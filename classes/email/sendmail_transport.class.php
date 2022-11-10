<?php


class sendmail_transport extends mail_transport{

    public function send($envelope){
        // CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
        if (!empty($envelope->sender) && $this->isShellSafe($envelope->sender)) {
            if ($this->config['qmail']){
                $sendmailFmt = '%s -f%s';
            }
            else{
                $sendmailFmt = '%s -oi -f%s -t';
            }
        }
        else {
            if ($this->config['qmail']){
                $sendmailFmt = '%s';
            }
            else{
                $sendmailFmt = '%s -oi -t';
            }
        }

        $sendmail = sprintf($sendmailFmt, escapeshellcmd($this->config['path']), $envelope->sender);

        foreach($envelope->to as $addr){
            $mail = @popen($sendmail, 'w');
            if (!$mail) {
                trigger_error('(sendmail_transport): Could not open sendmail process',E_USER_WARNING);
                return false;
            }
            fwrite($mail, 'To: ' . $addr . "\n");
            fwrite($mail, $envelope->header);
            fwrite($mail, $envelope->body);
            $result = pclose($mail);
            if ($result !== 0) {
                trigger_error('(sendmail_transport): Could not write message',E_USER_WARNING);
                return false;
            }
        }
        return true;
    }
}
