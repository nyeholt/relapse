<?php

class SmsService
{
	private $username;
	private $password;
	private $from;
	
	public function __construct()
	{
		$this->username = za()->getConfig('smsuser');
		$this->password = za()->getConfig('smspass');
		$this->from = za()->getConfig('name');
	}

	/**
	 * Send a message to a given mobile number
	 * 
	 * @param $message
	 * 			The message to send
	 * @param $to
	 * 			The number to send to
	 * 
	 * @return 
	 * 			The message ID
	 */
	public function send($message, $to)
	{
		if ($this->username && $this->password) {
			$queryString = "http-api.php?action=sendsms&user=".$this->username."&password=".$this->password;
			$queryString .= "&from=".rawurlencode($this->from)."&to=".rawurlencode($to);
			$queryString .= "&clientcharset=ISO-8859-1&";
			$queryString .= "text=".rawurlencode($message) . "&detectcharset=1";    	
			$url = 'http://www.smsglobal.com.au/'.$queryString;
			$this->log->debug("Sending sms to $to via $url");
			set_time_limit(120);
			$fd = @implode ('', file ($url));  	
			if ($fd) {  		
				// got response from server  		
				$response = split("; Sent queued message ID:",$fd);  		
				$response1 = split(":",$response[0]);  		
				$smsglobal_status = trim($response1[1]);  		
				$response2 = split(":",$response[1]);  		
				$smsglobalmsgid = trim($response2[1]);    		
				if ($smsglobal_status=="0") {  			
					// message sent successfully  			
					$ok = $smsglobalmsgid;  		
				} else {  			
					// gateway will issue a pause here and output will be delayed  			
					// possible bad user name and password  			
					$ok = false;  		
					$this->log->err("Bad status returned by SMS gateway");
					$this->log->err($response);
				}
			} else {  		
				// no contact with gateway  		
				$ok = false;  	
				$this->log->err("Failed connecting to SMS gateway");
			}  	
		} else {
			$this->log->err("No user/pass configured for sending SMS");
		}

		return $ok;
	}
}

?>