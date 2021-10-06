<?php

namespace app\mail;

include_once __DIR__ . "/mailHelpers.php";

/**
 *  E-mails sender
 *
 *  @property $_ds, Data storage instance
 *  @property $_db, Database access
 *  @property $_headers, E-mail headers
 *  @property $_data, E-mail data
 *  @property $_html, Html e-mail template
 */
class mailSender
{
    private
    $_headers = [],
    $_data = [],
    $_signature = '',
    $_html = "<html><body>%email%</body></html>",
    $_uid = '';
    
    public function __construct()
    {
		$this->_headers[] = "Date: " . rfcDate();
    }
    
    /**
     *  Send mail
     *
     *  @param string $to
     *  @param string $subject
     *  @param string $message
     *  @param string $file
     *
     *  @return boolean $result
     */
    public function send($to, $subject, $message, $file = null)
    {
        $this->_data['receiver'] = $to;
        $this->_data['mail'] = $message . $this->_signature;
        $this->_data['subject'] = $subject;
        
        $this->_setHeaders($message !== strip_tags($message), $message, $file);

        $this->_data['sent'] = $result['sent'] = (int) mail(
            $this->_data['receiver'],
            $this->_data['subject'],
            $this->_data['mail'],
            implode(EOL, $this->_headers) . EOL
        );
        
        $this->reset();
        
        return $result;
    }
    
    /**
     *  Set private headers array
     *
     *  @param boolean $isHtml
     *  @param string $mail
     *  @param string $file
     */
    private function _setHeaders($isHtml, $mail, $file = null)
    {
        $this->_uid = md5(uniqid(time()));
        
        if ($file && $file->getName()) {
            $this->_headers[] = 'Content-type: multipart/mixed; boundary="' . $this->_uid . '"';
            $this->_data['mail'] = implode(EOL, $this->_createFileMessage($isHtml, $file));
        }
        else {
			$type = "plain";
            if ($isHtml) {
                $type = "html";
                $this->_data['mail'] = str_replace("%email%", $this->_data['mail'], $this->_html);
            }
            $this->_headers[] = "Message-ID: " . $this->_uid;
			$this->_headers[] = "X-Mailer: PHP " . phpversion();
            $this->_headers[] = "MIME-Version: 1.0";
            $this->_headers[] = "Content-type: text/${type}; charset=" . CHARSET_ISO88591;
			$this->_headers[] = "Content-Transfer-Encoding: " . setEncoding($this->_data['mail']);
        }
    }
    
    /**
     *  Create message with file in attachment
     *
     *  @param boolean $isHtml
     *  @param string $file
     */
    private function _createFileMessage($isHtml, $file)
    {
        $contentType = $isHtml ? "html" : "plain";
        $content = $isHtml ? str_replace("%email%", $this->_data['mail'], $this->_html) : $this->_data['mail'];

        $imageName = $file->getSanitizedName();
        $newPath = __DIR__ . '/files/' . $imageName;
        $file->move($newPath);
        
        $fileContent = @file_get_contents($newPath);
        $fileContent = chunk_split(base64_encode($fileContent));
        $fileName = basename($newPath);
        
        $message = [];
        $message[] = "--" . $this->_uid;
        $message[] = "Content-type: text/" . $contentType . ", charset=utf-8";
        $message[] = "Content-Transfer-Encoding: " . setEncoding($content) . EOL;
        $message[] = $content . EOL;
        $message[] = "--" . $this->_uid;
        $message[] = 'Content-Type: application/octet-stream; name="' . $fileName . '"';
        $message[] = "Content-Transfer-Encoding: base64";
        $message[] = "Content-Disposition: attachment; filename=\"" . $fileName . EOL;
        $message[] = $fileContent . EOL;
        $message[] = "--" . $this->_uid;
        
        unlink($newPath);
        
        return $message;
    }
    
    /**
     *  Set from address
     *
     *  @param string $from
     *  @param string $fromName
     */
    public function setFrom($from, $fromName = null)
    {
        $this->_headers[] = "From: $fromName <$from>";
    }
    
    /**
     *  Set copy addresses
     *
     *  @param Array<string> $cc
     */
    public function setCc($cc)
    {
        $this->_headers[] = "Cc: " . implode(',', $cc);
    }
    
    /**
     *  Set hidden copy addresses
     *
     *  @param Array<string> $bcc
     */
    public function setBcc($bcc)
    {
        $this->_headers[] = "Bcc: " . implode(',', $bcc);
    }
        
    /**
     *  Set reply address
     *
     *  @param string $reply
     */
    public function setReplyTo($reply)
    {
        $this->_headers[] = "Reply-To: $reply";
    }
        
    /**
     *  Set mail signature
     *
     *  @param string $reply
     */
    public function setSignature($signature)
    {
        $this->_signature = $signature;
    }
	
    /**
     *  Reset sender to initialize state
     */
    public function reset()
    {
        $this->_headers = [];
        $this->_headers[] = "Date: " . rfcDate();
        $this->_data = [];
        $this->_signature = '';
        $this->_uid = '';
    }
    
    /**
     *  Save E-mail to database
     *
     *  @param array $data, E-mail data to store
     *
     *  @return boolean Save result
     */
    private function _saveMail($data)
    {
        $queries = [
            'insert@emails' => [
                'affectedColumns' => [
                    'receiver' => "%data%",
                    'subject' => "%data%",
                    'mail' => "%data%",
                    'ts_created' => $this->_ds->time['timeStamp'],
                    'sent' => "%data%",
                ],
                'data' => [$data]
            ]
        ];
        
        $result = $this->_db->runTransaction($queries);
        
        return $result;
    }
    
    private function encrypt()
    {
        $headers = array(
            "From" => "someone@example.com",
            "To" => "someone-else@example.com",
            "Cc" => "spam@somewhere.org",
            "Subject" => "Encrypted mail readable with most clients",
            "X-Mailer" => "PHP/".phpversion()
        );

        // Get the public key certificate.
        $pubkey = file_get_contents("C:\test.cer");

        // Remove some double headers for mail()
        $headers_msg = $headers;

        unset($headers_msg['To'], $headers_msg['Subject']);

        $data = "<<
        This email is Encrypted!
        You must have my certificate to view this email!
        Me
        EOD";

        //write msg to disk
        $fp = fopen("C:\msg.txt", "w");
        fwrite($fp, $data);
        fclose($fp);

        // Encrypt message
        openssl_pkcs7_encrypt("C:\msg.txt", "C:\enc.txt", $pubkey, $headers_msg, PKCS7_TEXT, 1);

        // Seperate headers and body for mail()
        $data = file_get_contents("C:\enc.txt");
        $parts = explode("\n\n", $data, 2);

        // Send mail
        mail($headers['To'], $headers['Subject'], $parts[1], $parts[0]);

        // Remove encrypted message (not fot debugging)
        //unlink("C:\msg.txt");
        //unlink("C:\enc.txt");
    }
}
