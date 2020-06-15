<?php

class horizonUrl {
	private $_url;
	private $_options;
	private $_headers;
	private $_cookies;
	private $_postdata;
	private $_response_headers;
	private $_channel;
	private $_response_code;
	private $_body;
	public $errormsg;
	public $error;
	
	function __construct(){
		$this->_channel = curl_init();
		curl_setopt($this->_channel,CURLOPT_AUTOREFERER,true);
		curl_setopt($this->_channel,CURLOPT_BINARYTRANSFER,true);
		curl_setopt($this->_channel,CURLOPT_ENCODING,'gzip');
		curl_setopt($this->_channel,CURLOPT_HEADER,1);
		curl_setopt($this->_channel,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
		curl_setopt($this->_channel,CURLOPT_MAXREDIRS,10);
		curl_setopt($this->_channel,CURLOPT_NOBODY,false);
		curl_setopt($this->_channel,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($this->_channel,CURLOPT_TIMEOUT,30);
		curl_setopt($this->_channel,CURLOPT_VERBOSE, 0);
	}
	function __destruct(){
		curl_close($this->_channel);
	}
	
	private function handleException($e){
		echo 'EXCEPTION: '."<br/>\n";
		echo 'Message: ' .$e->getMessage()."<br/>\n";
		echo 'Line: ' .$e->getLine()."<br/>\n";
		echo 'File: ' .$e->getFile()."<br/>\n";
	}

	private function setRequestHeader($header){
		if (is_array($header) && count($header) > 0){
			$tmpheader = array();
			foreach($header as $key => $val){
				$tmpheader[] = $key.': '.$val;
			}
			curl_setopt($this->_channel,CURLOPT_HTTPHEADER,$tmpheader);
		}
	}

	private function prepareRequest($type, $url, $header = null, $data = null){
		try {
			if ($url != ''){
				$this->_url = $url;
				$this->setRequestHeader($header);
				return true;
			}
			return false;
		} catch(Exception $e) {
			$this->handleException($e);
			return false;
		}
	}

	public function setBasicAuth($username,$password){
		curl_setopt($this->_channel,CURLOPT_HTTP_VERSION,CURLAUTH_ANY);
		curl_setopt($this->_channel, CURLOPT_USERPWD, $username.":".$password);
	}
	
	public function getBody(){
		return $this->_body;
	}
	
	public function getRequestHeaders(){
		return $this->_headers;
	}
	
	public function get($url, $header = null){
		$this->prepareRequest('get',$url, $header);
		curl_setopt($this->_channel,CURLOPT_URL,$url);
		return $this->exec();
	}

	public function post($url, $data, $header = null){
		$this->prepareRequest('post',$url, $header, $data);
		curl_setopt($this->_channel,CURLOPT_URL,$url);
		curl_setopt($this->_channel,CURLOPT_POST, 1);
		curl_setopt($this->_channel, CURLOPT_POSTFIELDS, $data);
		return $this->exec();
	}

	public function put($url, $data, $header = null){
		$this->prepareRequest('put',$url, $header, $data);
		curl_setopt($this->_channel,CURLOPT_URL,$url);
		curl_setopt($this->_channel,CURLOPT_PUT, 1);
		return $this->exec();
	}

	private function getInfo(){
			$info = curl_getinfo($this->_channel);
			if (is_array($info)){
				if (isset($info['http_code']) && intval($info['http_code']) > 0){
					$this->_response_code = $info['http_code'];
				}
			}
			return true;
	}
	
	private function exec(){
		try {
			$response = curl_exec($this->_channel);
			$this->error = curl_errno($this->_channel);
			if($this->error){
				$this->errormsg = curl_error($this->_channel);
				throw new Exception($this->errormsg,$this->error);
				
			}
			if ($this->getInfo()){
				switch($this->_response_code){
					case 200 :
					break;
					case 400 :
						throw new Exception('Bad Request URL: '.$this->_url,$this->_response_code);
					break;
					case 401 :
						throw new Exception('Unauthorized URL: '.$this->_url,$this->_response_code);
					break;
					case 403 :
						throw new Exception('Forbidden URL: '.$this->_url,$this->_response_code);
					break;
					case 404 :
						throw new Exception('File not found: '.$this->_url,$this->_response_code);
					break;
					case 405 :
						throw new Exception('Method not allowed: '.$this->_url,$this->_response_code);
					break;
					case 406 :
						throw new Exception('Not acceptable: '.$this->_url,$this->_response_code);
					break;
					case 410 :
						throw new Exception('Gone: '.$this->_url,$this->_response_code);
					break;
					case 500 :
						throw new Exception('Internal Serrver Error: '.$this->_url,$this->_response_code);
					break;
					case 501 :
						throw new Exception('Not implemented: '.$this->_url,$this->_response_code);
					break;
					case 502 :
						throw new Exception('Bad Gateway: '.$this->_url,$this->_response_code);
					break;
					case 503 :
						throw new Exception('Service Unavailable: '.$this->_url,$this->_response_code);
					break;
					case 504 :
						throw new Exception('Gateway timeout: '.$this->_url,$this->_response_code);
					break;
					default :
						throw new Exception('Unhandled status code ('.$this->_response_code.'): '.$this->_url,$this->_response_code);
					break;
				}

				$header_size = curl_getinfo($this->_channel, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				$headerarray = explode("\n",trim($header));
				foreach($headerarray as $key => $val){
					if (preg_match("/^([^\:]+)\:(.*)$/",$val,$regs)){
						$this->_headers[trim($regs[1])] = trim($regs[2]);
					}
				}
				$this->_body = substr($response, $header_size);
				return true;
			}
			return false;
		} catch(Exception $e) {
			$this->handleException($e);
			return false;
		}
	}
	
	public static function parseUrl($url){
		$retVal = array();
		$info = parse_url($url);
		$path = $info['path'];
		$pathinfo = pathinfo($path);
		$retVal = array_merge($info,$pathinfo);
		return $retVal;
	}
}

?>