<?php

class horizonException {

	private static $envtype = null;
	public static $createResopnse = false;
	
	public static function handleError($code, $text, $file, $line){
		switch($code){
			case E_USER_ERROR :
			break;
			case E_USER_WARNING :
			break;
			case E_USER_NOTICE :
			break;
			default :
			break;
		}
		return true;
	}
	
	public static function handleException($e){
		$retVal = self::createObject($e);
		switch($retVal['type']){
			case 'horizonMySQLException' :
				$params = $e->getParams();
				$retVal['additional'] = print_r($params,true);
			break;
			case 'horizonFileException' :
			default :
			break;
			
		}
		$type = self::getType();
		switch($type){
			case 'text/plain' :
				self::createCliResponse($retVal);
			break;
			case 'text/xml' :
				self::createXMLResponse($retVal);
				return;
				if (isset(horizonRequest::$transport) && horizonRequest::$transport != null && horizonRequest::$transport == 'xmlsoap'){
					self::createSoapResponse($retVal);
				} else {
					self::createXMLResponse($retVal);
				}
			break;
			case 'application/json' :
				self::createJSONResponse($retVal);
			break;
			default :
				self::createHTMLResponse($retVal);
			break;
		}
	}
	
	private static function createObject($e){
		$type = self::getType();
		$retVal = array();
		$code = $e->getCode();
		$retVal['message'] = $e->getMessage();
		$retVal['code'] = $code;
		$retVal['file'] = $e->getFile();
		$retVal['line'] = $e->getLine();
		$retVal['type'] = get_class($e);
		$trace = $e->getTrace();
		if (isset($trace) && is_array($trace) && count($trace) > 0){
			foreach($trace as $key => $value){
				$tmp = array();
				$tmp['file'] = $value['file'];
				$tmp['line'] = $value['line'];
				$tmp['function'] = $value['function'];
				$tmp['class'] = $value['class'];
				$tmp['type'] = $value['type'];
				$tmp['args'] = array();
				foreach($value['args'] as $innerkey => $innervalue){
					$innertmp = array();
					$innertmp['type'] = gettype($innervalue);
					switch($innertmp['type']){
						case 'array' :
							$innertmp['value'] = json_encode($innervalue,JSON_PRETTY_PRINT);
						break;
						case 'object' :
						case 'unknown' :
							$innertmp2 = print_r($innervalue,true);
							$innertmp['value'] = $innertmp2;
						break;
						default :
							$innertmp['value'] = $innervalue;
						break;
					}

					$tmp['args'][] = $innertmp;
				}
			}
			$retVal['trace'][] = $tmp;
		}
		return $retVal;
	}
	

	private static function getType(){
		self::$envtype = php_sapi_name();
		$default = ini_get('default_mimetype');
		switch(self::$envtype){
			case 'cli' :
				return 'text/plain';
			break;
			default :
				$tmpheaders = headers_list();
				foreach($tmpheaders as $key => $value){
					if (preg_match("/^content-type\s*:\s*([^;]+)(;.*)?$/i",$value,$regs)){
						if (trim($regs[1]) != '') {
							return trim($regs[1]);
						}
					}
				}
				if ($default != ''){
					return $default;
				}
				return 'unknown';
			break;
		}
	}
	
	private static function array2string($data){
		$log_a = "";
		foreach ($data as $key => $value) {
			if(is_array($value)){
				$log_a .= "[".$key."] => (". self::array2string($value). ")<br/>";
			} else {
				$log_a .= "[".$key."] => ".$value."\n";
			}
			               
		}
		return $log_a;
	}

	private static function createHtmlResponse($obj){
			$nonce = md5(rand().'-'.uniqid());
			http_response_code (500);
			header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-".$nonce."'; style-src 'self' 'nonce-".$nonce."' ",true);
				#exit;
			echo "<!doctype html>\n";
			echo "<html>\n";
			echo "	<head>\n";
			echo '		<meta charset="utf-8"/>'."\n";
			echo "		<title>FATAL ERROR</title>\n";
			echo '		<link rel="stylesheet" href="/static/exception.css">'."\n";
			echo "	</head>\n";
			echo "	<body>\n";
			echo '		<div class="fatal">'."\n";
			echo '			<div class="wrapper">'."\n";
			echo '				<table class="outer">'."\n";
			echo '					<tr><th class="h1" colspan="2">Fatal Error</th></tr>'."\n";
			echo '					<tr><td colspan="2" class="message">'.$obj['message'].'</td></tr>'."\n";
			echo '					<tr><td class="first">Type:</td><td class="type">'.$obj['type'].'</td></tr>'."\n";
			echo '					<tr><td class="first">Error Number:</td><td class="code">'.$obj['code'].'</td></tr>'."\n";
			echo '					<tr><td class="first">File:</td><td class="file">'.$obj['file'].'</td></tr>'."\n";
			echo '					<tr><td class="first">Line:</td><td class="line">Line: '.$obj['line'].'</td></tr>'."\n";
			echo '					<tr><th colspan="2">Trace:</th></tr>'."\n";
			if (isset($obj['trace']) && is_array($obj['trace']) && count($obj['trace']) > 0) {
				foreach($obj['trace'] as $key => $value){
					echo '						<tr><td class="first">File:</td><td class="tracefile">'.$value['file'].'</td></tr>'."\n";
					echo '						<tr><td class="first">Line:</td><td class="traceline">'.$value['line'].'</td></tr>'."\n";
					echo '						<tr><td class="first">Function:</td><td class="tracefunction">'.$value['class'].''.$value['type'].''.$value['function'].'</td></tr>'."\n";
					if (is_array($value['args']) && count($value['args']) > 0){
						echo '							<tr>'."\n";
						echo '								<th class="first">type</th>'."\n";
						echo '								<th>value</th>'."\n";
						echo '							</tr>'."\n";
						foreach($value['args'] as $innerkey => $innervalue){
							echo '							<tr>'."\n";
							echo '								<td class="first">'.$innervalue['type'].'</td>'."\n";
							echo '								<td class="argvalue"><div>'.nl2br(htmlentities($innervalue['value'])).'</div></td>'."\n";
							echo '							</tr>'."\n";
						}
					}
	/*
	*/
				}
			}
			if($obj['additional'] != ''){
				echo '					<tr><td class="first">Additional:</td><td class="additional">'.$obj['additional'].'</td></tr>'."\n";
			}
			echo '			</table>'."\n";
			echo '			</div>'."\n";
			echo '		</div>'."\n";
			echo "	</body>\n";
			echo "</html>\n";
			exit;
	}

	private static function createSoapResponse($obj){
		$tmp = print_r($obj,true);
		error_log($tmp);
		$msg = '';
		$msg .= '	<exception errorcode="'.$obj['code'].'" type="'.$obj['type'].'">'."\n";
		$msg .= "		<title><![CDATA[FATAL ERROR]]></title>\n";
		$msg .= '		<message><![CDATA['.$obj['message'].']]></message>'."\n";
		$msg .= '		<file><![CDATA['.$obj['file'].']]></file>'."\n";
		$msg .= '		<line>'.$obj['line'].'</line>'."\n";
		if (isset($obj['trace']) && is_array($obj['trace']) && count($obj['trace']) > 0) {
			$msg .= '		<trace>'."\n";
			foreach($obj['trace'] as $key => $value){
				$msg .= '			<file><![CDATA['.$value['file'].']]></file>'."\n";
				$msg .= '			<line>'.$value['line'].'</line>'."\n";
				$msg .= '			<function><![CDATA['.$value['class'].''.$value['type'].''.$value['function'].']]></function>'."\n";
				if (is_array($value['args']) && count($value['args']) > 0) {
					echo '			<arguments>'."\n";
					foreach($value['args'] as $innerkey => $innervalue){
						$msg .= '				<argument type="'.$innervalue['type'].'">';
						$msg .= '<![CDATA['.nl2br(htmlentities($innervalue['value'])).']]>';
						$msg .= '</argument>'."\n";
					}
					$msg .= '			</arguments>'."\n";
				}
			}
			$msg .= '		</trace>'."\n";
		}
		if(isset($obj['additional']) && $obj['additional'] != ''){
			$msg .= '					<tr><td class="first">Additional:</td><td class="additional">'.$obj['additional'].'</td></tr>'."\n";
		}
		$msg .= "	<exception>\n";
		
		horizonSOAPService::$soapserver->faultSoapFault('Exception',$msg);
	}
	
	private static function createXmlResponse($obj){
		http_response_code (500);
		echo '<?xml version="1.0" encoding="utf-08"?'.'>'."\n";
		echo '<response type="exception">'."\n";
		echo '	<exception errorcode="'.$obj['code'].'" type="'.$obj['type'].'">'."\n";
		echo "		<title><![CDATA[FATAL ERROR]]></title>\n";
		echo '		<message><![CDATA['.$obj['message'].']]></message>'."\n";
		echo '		<file><![CDATA['.$obj['file'].']]></file>'."\n";
		echo '		<line>'.$obj['line'].'</line>'."\n";
		if (isset($obj['trace']) && is_array($obj['trace']) && count($obj['trace']) > 0) {
			echo '		<trace>'."\n";
			foreach($obj['trace'] as $key => $value){
				echo '			<file><![CDATA['.$value['file'].']]></file>'."\n";
				echo '			<line>'.$value['line'].'</line>'."\n";
				echo '			<function><![CDATA['.$value['class'].''.$value['type'].''.$value['function'].']]></function>'."\n";
				if (is_array($value['args']) && count($value['args']) > 0) {
					echo '			<arguments>'."\n";
					foreach($value['args'] as $innerkey => $innervalue){
						echo '				<argument type="'.$innervalue['type'].'">';
						echo '<![CDATA['.nl2br(htmlentities($innervalue['value'])).']]>';
						echo '</argument>'."\n";
					}
					echo '			</arguments>'."\n";
				}
			}
			echo '		</trace>'."\n";
		}
		if(isset($obj['additional']) && $obj['additional'] != ''){
			echo '					<tr><td class="first">Additional:</td><td class="additional">'.$obj['additional'].'</td></tr>'."\n";
		}
		echo "	<exception>\n";
		echo '</response>'."\n";
		exit;
	}
	private static function createJSONResponse($obj){
		http_response_code (500);
		echo json_encode($obj,JSON_PRETTY_PRINT);
		exit;
	}

	private static function createCliResponse($obj){
		$response = '';
		$response .= "Message: ".$obj['message'].PHP_EOL;
		$response .= "type:    ".$obj['type'].PHP_EOL;
		$response .= "code:    ".$obj['code'].PHP_EOL;
		$response .= "File:    ".$obj['file'].PHP_EOL;
		$response .= "Line:    ".$obj['line'].PHP_EOL;
		if (isset($obj['trace']) && is_array($obj['trace']) && count($obj['trace']) > 0) {
			$response .= str_repeat("=",80).PHP_EOL;
			$response .= "  TRACE:".PHP_EOL;
			foreach($obj['trace'] as $key => $value){
				$response .= "  File:     ".$value['file'].PHP_EOL;
				$response .= "  Line:     ".$value['line'].PHP_EOL;
				$response .= "  Function: ".$value['class'].''.$value['type'].''.$value['function'].PHP_EOL;
				if (is_array($value['args']) && count($value['args']) > 0){
					$response .= '  '.str_repeat("=",78).PHP_EOL;
					$response .= "    ARGUMENTS".PHP_EOL;
					foreach($value['args'] as $innerkey => $innervalue){
						$response .= '    Type:  '.$innervalue['type'].PHP_EOL;
						$response .= '    Value: '.$innervalue['value'].''.PHP_EOL;
					}
				}
			}
		}
		$response .= PHP_EOL;
		switch(self::$envtype){
			case 'cli' :
			case 'cli-server' :
				$headline = "FATAL ERROR:".str_repeat(' ',68);
				$headline = horizonShell::getColoredString($headline,'red','light_gray');
				$response = PHP_EOL.$headline.PHP_EOL.$response;
				horizonShell::writeStdErr($response);
			break;
			default :
				echo $response;
			break;
			
		}
		exit(1);
	}

}
?>