<?php
abstract class dwdCapMessageBase {

	protected $area = null;
	protected $areaColor = null;
	protected $category = null;
	protected $code = array();
	protected $contact = null;
	protected $effective = null;
	protected $event = null;
	protected $eventName = null;
	protected $expires = null;
	protected $groups = null;
	protected $identifier = null;
	protected $iicode = null;
	protected $info = array();
	protected $license = null;
	protected $msgType = null;
	protected $onset = null;
	protected $profileVersion = null;
	protected $references = array();
	protected $responseType = null;
	protected $sender = null;
	protected $senderName = null;
	protected $severity = null;
	protected $scope = null;
	protected $sent = null;
	protected $source = null;
	protected $status = null;
	protected $urgency = null;
	protected $web = null;

	public function processNotification(){
		$retVal = array(
			'alert' => array(),
			'update' => array(),
			'cancel' => array()
		);
		if (isset($this->status) && $this->status == 'Test'){
			# Test - nothing to do
			return $retVal;
		}
		if (isset($this->msgType)){
			if ($this->msgType == 'Cancel' ){
				# Message not valid - nothing to do
				return $retVal;
			} elseif ($this->msgType == 'Update' ){
				if (is_array($this->code)){
					foreach($this->code as $key => $val){
						if ($val == 'SILENT_UPDATE') {
							# Silent update - nothing to do
							return $retVal;
						}
						if ($val == 'PARTIAL_CLEAR') {
							# Parts of the message are cancelled - notify onliy these parts
							if ($this->responseType == 'AllClear'){
								$warncells = $this->getWarnCells();
								$retVal['cancel'] = $warncells;
								return $retVal;
							}
						}
					}
				}
				if ($this->responseType == 'Prepare'){
					# message with instructions
					$warncells = $this->getWarnCells();
					$retVal['alert'] = $warncells;
					return $retVal;
				} elseif ($this->responseType == 'Monitor'){
					# test message - ignoring
					return $retVal;
				} elseif ($this->responseType == 'None'){
					# message without instructions
					$warncells = $this->getWarnCells();
					$retVal['alert'] = $warncells;
					return $retVal;
				} elseif ($this->responseType == 'AllClear'){
					# Message is cancelled 
					$warncells = $this->getWarnCells();
					$retVal['cancel'] = $warncells;
					return $retVal;
				} elseif ($this->responseType == ''){
				}
			}

			if ($this->msgType == 'Alert' || 1 == 1){
				$warncells = $this->getWarnCells();
				$retVal['alert'] = $warncells;
				return $retVal;
			}
		}
	}
	
	public function getWarnCells(){
		$retVal = array();
		if (isset($this->info)){
			if (is_array($this->info)){
				foreach($this->info as $key => $val){
					$retVal[] = array_merge($retVal,$val->getWarnCells());
				}
			}
		}
		return $retVal;
	}

	public function getHeadline(){
		$retVal = array();
		$tmp = array();
		if (isset($this->info)){
			if (is_array($this->info)){
				foreach($this->info as $key => $val){
					$tmp[] = $val->getHeadline();
				}
			}
		}
		foreach($tmp as $key => $val){
			$retVal[$val['lang']] = $val['value'];
		}
		return $retVal;
	}

	public function getMessage(){
		$retVal = array();
		$tmp = array();
		if (isset($this->info)){
			if (is_array($this->info)){
				foreach($this->info as $key => $val){
					$tmp[] = $val->getMessage();
				}
			}
		}
		foreach($tmp as $key => $val){
			$retVal[$val['lang']] = $val['value'];
		}
		return $retVal;
	}

	public function getInstruction(){
		$retVal = array();
		$tmp = array();
		if (isset($this->info)){
			if (is_array($this->info)){
				foreach($this->info as $key => $val){
					$tmp[] = $val->getInstruction();
				}
			}
		}
		foreach($tmp as $key => $val){
			$retVal[$val['lang']] = $val['value'];
		}
		return $retVal;
	}

	public function getIdentifier(){
		if (isset($this->identifier)){
			if (is_array($this->identifier)){
				return $this->identifier;
			}
		}
		return null;
	}

	public function hasInfo(){
		$ident = $this->getIdentifier();
		if(is_array($ident) && isset($ident['uuid'])){
			$uuid = $ident['uuid'];
			if (isset($this->code)){
				if (is_array($this->code)){
					foreach($this->code as $key => $val){
						$tmpcode = $this->parseIdentifier($val);
						if (is_array($tmpcode)){
							if ($tmpcode['uuid'] == $uuid) {
								return true;
							}
						}
					}
				}
			}
			
		}
		return false;
	}


	public function __construct($obj){
		
		if (is_object($obj) && get_class($obj) == 'SimpleXMLElement'){
			foreach($obj->children() as $item){
				$codehasid = false;
				$name = $item->getName();
				switch($name){
					case 'identifier':
						$this->identifier = $this->parseIdentifier((string) $item);
					break;
					case 'sender':
					case 'sent':
						$this->$name = (string) $item;
					break;
					case 'status':
					case 'msgType':
					case 'source':
					case 'scope':
						$this->$name = (string) $item;
					break;
					case 'code':
						$code = (string) $item;
						switch($code){
							case 'SILENT_UPDATE' :
								if ($this->msgType == 'Update'){
									$this->code[] = $code;
								} else {
									throw new Exception('code does not match msgType',1005);
								}
							break;
							case 'PARTIAL_CLEAR' :
								# TODO responseType
								if ($this->msgType == 'Update'){
									$this->code[] = $code;
								} else {
									throw new Exception('code does not match msgType',1006);
								}
							break;
							default :
								$tmp = $this->parseIdentifier($code);
								if (is_array($tmp)){
									if (!$codehasid){
										$this->code[] = $code;
										$codehasid = true;
									} else {
										throw new Exception('duplicate id in code Element'.$code,1007);
									}
								} else {
									throw new Exception('Unkown code '.$code,1004);
								}
							break;
							
						}
					break;
					case 'references':
						$tmpref = (string) $item;
						$tmprefarray = explode(' ',$tmpref);
						foreach($tmprefarray as $key => $val){
							$tmparray = explode(',',$val);
							if (count($tmparray) == 3){
								$tmp = $this->parseIdentifier($tmparray[1]);
								if (is_array($tmp)){
									$this->references[] = array(
										"sender" => $tmparray[0],
										"identifier" => $this->parseIdentifier($tmparray[1]),
										"sent" => $tmparray[2]
									);
								}
							} else {
								echo 'Unkown references format '.$val;
								throw new Exception('Unkown references format '.(string) $item,1004);
							}
						}
					break;
					case 'info':
						try {
							$info = new dwdCapInfo($item);
							$this->severity = $info->severity;
							$this->category = $info->category;
							$this->certainty = $info->certainty;
							$this->urgency = $info->urgency;
							$this->responseType = $info->responseType;
							$this->event = $info->event;
							$this->eventName = $info->eventName;
							$this->effective = $info->effective;
							$this->onset = $info->onset;
							$this->expires = $info->expires;
							$this->web = $info->web;
							$this->contact = $info->contact;
							$this->senderName = $info->senderName;
							$this->parameter = $info->parameter;
							$this->area = $info->area;
							$this->groups = $info->groups;
							$this->iicode = $info->iicode;
							$this->license = $info->license;
							$this->profileVersion = $info->profileVersion;
							$this->areaColor = $info->areaColor;
							$this->info[] = $info;
						} catch (Exception $e) {
							$code = intval($e->getCode());
							switch($code){
								case 1000 :
									echo "Warning: ".$e->getMessage()."\n";
								break;
								default :
									echo $e->getMessage()."\n";
									echo $e->getCode()."\n";
									print_r($obj);
									die();
								break;
							}
						}
					break;
				}
				
			}
			if ($this->area == null){
				print_r($this);
				die('No Areas');
			}
			if ($this->senderName == null){
				print_r($this);
				die();
			}
		}
	}
	
	private function parseIdentifier($str){
		if (preg_match("/^(id\:)?2\.49\.0\.1\.276\.0\.([^\.]+)\.([^\.]+)\.([0-9]+)\.([^\.]+)(\.([^\.]+))?$/",$str,$regs)){
			return array(
				"owner" => $regs[2],
				"system" => $regs[3],
				"timestamp" => $regs[4],
				"uuid" => $regs[5],
				"suffix" => (isset($regs[7]) ? $regs[7] : '')
			);
		}
	}

	protected static function handleException($e){
		$msg = $e->getMessage();
		$code = $e->getCode();
		$str = '';
		switch($code){
			default :
				$str = "FATAL Error: (".$code.") ".$msg;
			break;
			
		}
		if (php_sapi_name() === 'cli'){
			echo $str.PHP_EOL;
		} else {
			echo $str.'<br/>'.PHP_EOL;
		}
	}


}
?>