<?php
abstract class dwdCapAreaBase {


	protected $areaDesc = null;
	protected $altitude = null;
	protected $ceiling = null;
	protected $polygon = null;
	protected $excludePolygon = null;
	protected $warncells = null;
	protected $type = 'warncell';

	public function getWarnCells(){
		$retVal = array();
		if (isset($this->warncells)){
			if (is_array($this->warncells)){
				foreach($this->warncells as $key => $val){
					if (is_array($val)){
						if (isset($val['warncellId'])){
							$retVal[] = $val['warncellId'];
						}
					}
				}
			}
		}
		return $retVal;
	}

	public function __construct($obj){
		if (is_object($obj) && get_class($obj) == 'SimpleXMLElement'){
			foreach($obj->children() as $item){
				$name = $item->getName();
				#echo $name."\n";
				switch($name){
					case 'areaDesc':
					case 'altitude':
					case 'ceiling':
						$this->$name = utf8_decode((string) $item);
					break;
					case 'polygon':
						if ($this->polygon === null){
							$this->polygon = array();
						}
						$this->polygon[] = (string) $item;
						$this->type = 'polygon';
					break;
					case 'geocode':
						$valueName = (string) $item->valueName;
						switch($valueName){
							case 'WARNCELLID' :
								$value = (string) $item->value;
								if ($this->warncells === null){
									$this->warncells = array();
								}
								if (preg_match("/^([0-9]*)([0-9]{2})([0-9]{3})([0-9]{3})$/",$value,$regs)){
									$this->warncells[] = array("type" => $regs[1], "state" => $regs[2], "county" => $regs[3], "city" => $regs[4], "warncellId" => $regs[0],"code" => $regs[2].$regs[3].$regs[4]);
								}
								
							break;
							case 'EXCLUDE_POLYGON' :
								$value = (string) $item->value;
								if ($this->excludePolygon === null){
									$this->excludePolygon = array();
								}
								$this->excludePolygon[] = $value;
								$this->type = 'excludepolygon';
							break;
						}
					break;
					default :
						throw new Exception('Unkown tag',10012);
					break;
				}
			}
		}
	}
	
	private function setState($warncell){
		$warncell = (string) $wancell;
		$state = substr($warncell,0,2);
		$county = substr($warncell,2,3);
		$city = substr($warncell,2,3);
		return array();
	}
}
?>