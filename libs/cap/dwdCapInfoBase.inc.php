<?php
abstract class dwdCapInfoBase {
	
	public $area = array();
	public $areaColor = '';
	public $category = null;
	public $certainty = null;
	public $contact = null;
	public $effective = null;
	public $event = null;
	public $eventName = '';
	public $expires = null;
	public $groups = array();
	public $iicode = 0;
	public $license = '';
	public $onset = null;
	public $parameter = array();
	public $profileVersion = '';
	public $responseType = null;
	public $senderName = null;
	public $severity = null;
	public $urgency = null;
	public $web = null;

	protected $description = null;
	protected $headline = null;
	protected $instruction = null;
	protected $language = null;

	public function getWarnCells(){
		$retVal = array();
		if (isset($this->area)){
			if (is_array($this->area)){
				foreach($this->area as $key => $val){
					$retVal = array_merge($retVal,$val->getWarnCells());
				}
			}
		}
		return $retVal;
	}

	public function getIIcode(){
		return $this->iicode;
	}


	public function __construct($obj){
		if (is_object($obj) && get_class($obj) == 'SimpleXMLElement'){
			foreach($obj->children() as $item){
				$name = $item->getName();
				switch($name){
					case 'senderName':
						$value = trim((string) $item);
						if ($value != ''){
							$this->$name = $value;
						}
					break;
					case 'headline':
					case 'description':
					case 'instruction':
					case 'web':
					case 'contact':
						$value = trim((string) $item);
						if ($value != ''){
							$this->$name = $value;
						}
					break;
					case 'event':
						$this->eventName = (string) $item;
					break;
					case 'language':
					case 'effective':
					case 'onset':
					case 'expires':
						$this->$name = (string) $item;
					break;
					
					case 'responseType':
					case 'certainty':
					case 'urgency':
					case 'category':
					case 'severity':
						$this->$name = (string) $item;
					break;
					case 'parameter':
						$valueName = (string) $item->valueName;
						$value = (string) $item->value;
						if (preg_match("/([^\[]+)\s*\[([^\]]+)\]/",$value,$regs)){
							$value = $regs[1];
							$unit = $regs[2];
						} else {
							$unit = null;
						}
						$this->parameter[$valueName][] = array('value' => $value,'unit' => $unit);
					break;
					case 'eventCode':
						$valueName = (string) $item->valueName;
						$value = (string) $item->value;

						if ($valueName == 'GROUP'){
							$this->groups[] = $value;
							
						} elseif ($valueName == 'II'){
							$this->iicode = $value;
							
						} elseif ($valueName == 'PROFILE_VERSION'){
							$this->profileVersion = $value;
							
						} elseif ($valueName == 'LICENSE'){
							$this->license = $value;
							
						} elseif ($valueName == 'AREA_COLOR'){
							$this->areaColor = $value;
							
						} elseif (isset($this->$name[$valueType])){
							if (!is_array($this->$name[$valueType])) {
								$this->$name[$valueType] = array($this->$name[$valueType]);
							}
							$this->$name[$valueType][] = $value;
							
						} else {
							$this->$name[$valueType] = $value;
						}
					break;
					case 'area':
						$this->area[] = new dwdCapArea($item);
					break;
				}
			}
			if ($this->senderName == null){
				throw new Exception("Cannot determine senderName ".$this->senderName,1001);
			}
			if ($this->iicode == null){
				throw new Exception("iiCode not valid ".$this->iicode,1002);
			}
			if ($this->expires == null && $this->senderName == 'DWD / Seewetterdienst Hamburg'){
				if ($this->onset != null ){
					$time = strtotime($this->onset);
				} else {
					$time = time();
				}
				$this->expires = date('Y-m-d\TH:i:sP',$time + 24*60*60);
			}
			
		}
	}

}

?>