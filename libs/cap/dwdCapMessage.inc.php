<?php
# exception id = 1018
class dwdCapMessage extends dwdCapMessageBase {

	static $_instance;
	private $db;
	/**
	* Function to add a new entry to the database.
	*
	*/
	
	public function __construct($obj){
		parent::__construct($obj);
		self::$_instance = $this;
		#self::clear_tables();

		$tmp = array();
		$types = array('urgency','certainty','responseType','category','severity','msgType','source','scope','status','code');
		foreach($types as $key => $val){
			$query = 'SELECT '.$val.'_name, '.$val.'_id FROM cap_types_'.$val;
			if (horizonMySQL::query($query)){
				while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
					$tmp[$result[$val.'_name']] = $result[$val.'_id'];
				}
				self::set('cap_types_'.$val,$tmp);
			}
		}

		$tmp = array();
		$query = "SELECT event_id,event_name,event_iicode,event_urgency FROM cap_types_event";
		if (horizonMySQL::query($query)){
			while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				$key = $result['event_name'];
				$tmp[$key][] = array(
					'urgency' => $result['event_urgency'],
					'ii' => $result['event_iicode'],
					'id' => $result['event_id']
				);
			}
			self::set('cap_types_event',$tmp);
		}
		$tmp = array();
		$query = "SELECT sender_id,senderName,web,contact,email FROM cap_data_sender";
		if (horizonMySQL::query($query)){
			while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				$key = $result['senderName'];
				$tmp[$key] = array(
					'web' => $result['web'],
					'contact' => $result['contact'],
					'email' => $result['email'],
					'id' => $result['sender_id']
				);
			}
			self::set('cap_types_sender',$tmp);
		}
		$tmp = array();
		$query = "SELECT group_id,group_name FROM cap_types_group";
		if (horizonMySQL::query($query)){
			while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				$key = $result['group_name'];
				$tmp[$key] = $result['group_id'];
			}
			self::set('cap_types_group',$tmp);
		}
		$tmp = array();
		$query = "SELECT license_id,license_name FROM cap_data_licenses";
		if (horizonMySQL::query($query)){
			while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				$key = $result['license_name'];
				$tmp[$key] = $result['license_id'];
			}
			self::set('cap_types_licenses',$tmp);
		}
		$tmp = array();
		$query = "SELECT areaColor_id,areaColor_name FROM cap_data_areaColors";
		if (horizonMySQL::query($query)){
			while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				$key = $result['areaColor_name'];
				$tmp[$key] = $result['areaColor_id'];
			}
			self::set('cap_types_areaColors',$tmp);
		}
		$tmp = array();
		$query = "SELECT profileVersion_id,profileVersion_name FROM cap_data_profileVersions";
		if (horizonMySQL::query($query)){
			while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				$key = $result['profileVersion_name'];
				$tmp[$key] = $result['profileVersion_id'];
			}
			self::set('cap_types_profileVersions',$tmp);
		}

		$tmp = array();
		$query = "SELECT type_id,name FROM cap_data_parameters_type";
		if (horizonMySQL::query($query)){
			while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				$key = $result['name'];
				$tmp[$key] = $result['type_id'];
			}
			self::set('cap_data_parameters_type',$tmp);
		}
		$tmp = array();
		$query = "SELECT unit_id,name FROM cap_data_parameters_units";
		if (horizonMySQL::query($query)){
			while($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				$key = $result['name'];
				$tmp[$key] = $result['unit_id'];
			}
			self::set('cap_data_parameters_units',$tmp);
		}
	}

	public function toSql(){

		try {
			$senderid = 0;
			if ($this->identifier['suffix'] == 'DEU'){
				
				foreach(array('responseType','certainty','urgency','category','severity') as $key => $name){
					$tmp = (string) $this->$name;
					$tmptypes = dwdCapMessage::get('cap_types_'.$name);
					if (isset($tmptypes[$tmp])){
						$this->$name = $tmptypes[$tmp];
					} else {
						throw new Exception($tmp.' not found in cap_types_'.$name,1000);
					}
				}


				$codeid = null;
				if (is_array($this->code) && count($this->code) > 0){
					$tmpcode = self::get('cap_types_code');
					foreach($this->code as $key => $code){
						if (isset($tmpcode[$code])){
							$codeid = $tmpcode[$code];
							break;
						}
					}
				}

				if ($this->eventName != ''){
					$tmpevent = self::get('cap_types_event');
					if (isset($tmpevent[$this->eventName])){
						foreach($tmpevent[$this->eventName] as $key => $val){
							if (isset($val['ii']) && intval($val['ii']) == intval($this->iicode)){
								if (isset($val['urgency']) && intval($val['urgency']) == intval($this->urgency)){
									$this->event = $val['id'];
									
								}
							}
						}
						if ($this->event == null){
							// There is a bug: the Seewetterdienst is sending wrong iicodes
							if ($this->senderName == 'DWD / Seewetterdienst Hamburg' && ($this->iicode == 11 || $this->iicode == 12)){
								// Do nothing
							} else {
								echo "Warning: - Cannot determine event ".$this->eventName." (iicode:".$this->iicode.")\n";
							}
						}
					} else {
						echo "Warning ".$this->eventName." not found\n";
					}
				}

				if ($this->senderName !== null && $this->web !== null && $this->contact !== null ){
					$currentSenders = self::get('cap_types_sender');
					if (isset($currentSenders[$this->senderName])){
						if ($currentSenders[$this->senderName]['web'] == $this->web && $currentSenders[$this->senderName]['contact'] == $this->contact && $currentSenders[$this->senderName]['email'] == $this->sender ){
							$senderid = $currentSenders[$this->senderName]['id'];
						}
						
					} 
					if ($senderid == 0){
						$sendersql = '';
						$senderfields = array();
						$senderfields[] = array('name' => 'senderName', "value" => $this->senderName, "type" => PDO::PARAM_STR);
						$senderfields[] = array('name' => 'web', "value" => $this->web, "type" => PDO::PARAM_STR);
						$senderfields[] = array('name' => 'contact', "value" => $this->contact, "type" => PDO::PARAM_STR);
						$senderfields[] = array('name' => 'email', "value" => $this->sender, "type" => PDO::PARAM_STR);
						$senderid = horizonMySQL::insert('cap_data_sender',$senderfields);
						if ($senderid == 0) {
							throw new Exception('Could not insert sender',1013);
						}

						$currentSenders[$this->senderName] = array('web' => $this->web, 'contact' => $this->contact, 'id' => $senderid, 'email' => $this->sender);
						self::set('cap_types_sender',$currentSenders);
					}
				} else {
					throw new Exception('SenderName is null '.$this->senderName." ".$this->web." ".$this->contact,1016);
				}

				if ($this->license !== null ){
					$currentLicenses = self::get('cap_types_licenses');
					if (isset($currentLicenses[$this->license])){
						$licenseid = $currentLicenses[$this->license];
						
					} else {
						$licensesql = '';
						$licensefields = array();
						$licensefields[] = array('name' => 'license_name', "value" => $this->license, "type" => PDO::PARAM_STR,'unique' => true);
						$licenseid = horizonMySQL::insert('cap_data_licenses',$licensefields);
						if ($licenseid == 0) {
							throw new Exception('Could not insert license',1021);
						}

						$currentLicenses[$this->license] = $licenseid;
						self::set('cap_types_licenses',$currentLicenses);
					}
				} else {
					throw new Exception('license is  null '.$this->license,1022);
				}


				if ($this->areaColor !== null ){
					$currentAreaColors = self::get('cap_types_areaColors');
					if (isset($currentAreaColors[$this->areaColor])){
						$areaColorId = $currentAreaColors[$this->areaColor];
						
					} else {
						$areacolorsql = '';
						$areacolorfields = array();
						$areacolorfields[] = array('name' => 'areaColor_name', "value" => $this->areaColor, "type" => PDO::PARAM_STR,'unique' => true);
						$areaColorId = horizonMySQL::insert('cap_data_areaColors',$areacolorfields);
						if ($areaColorId == 0) {
							throw new Exception('Could not insert areaColor',1023);
						}

						$currentAreaColors[$this->areaColor] = $areaColorId;
						self::set('cap_types_areaColors',$currentAreaColors);
					}
				} else {
					throw new Exception('areaColor is null '.$this->areaColor,1024);
				}

				if ($this->profileVersion !== null ){
					$currentprofileVersions = self::get('cap_types_profileVersions');
					if (isset($currentprofileVersions[$this->profileVersion])){
						$profileVersionId = $currentprofileVersions[$this->profileVersion];
						
					} else {
						$fields = array();
						$fields[] = array('name' => 'profileVersion_name', "value" => $this->profileVersion, "type" => PDO::PARAM_STR,'unique' => true);
						$profileVersionId = horizonMySQL::insert('cap_data_profileVersions',$fields);
						if ($profileVersionId == 0) {
							throw new Exception('Could not insert profileVersion',1026);
						}

						$currentprofileVersions[$this->profileVersion] = $profileVersionId;
						self::set('cap_types_profileVersions',$currentprofileVersions);
					}
				} else {
					throw new Exception('profileVersion is null '.$this->profileVersion,1025);
				}
				
				foreach(array('status','msgType','source','scope') as $key => $name){
					$tmp = (string) $this->$name;
					$tmptypes = dwdCapMessage::get('cap_types_'.$name);
					if (isset($tmptypes[$tmp])){
						$this->$name = $tmptypes[$tmp];
					} else {
						throw new Exception($tmp.' not found in cap_types_'.$name,1000);
					}
				}

				$sql = '';
				$fields = array();
				$fields[] = array('name' => 'guid', "value" => $this->identifier['uuid'], "type" => PDO::PARAM_STR,'unique' => true);
				$fields[] = array('name' => 'sent', "value" => date('Y-m-d H:i:s',strtotime($this->sent)), "type" => PDO::PARAM_STR);
				$fields[] = array('name' => 'sender_id', "value" => $senderid, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'effective', "value" => date('Y-m-d H:i:s',strtotime($this->effective)), "type" => PDO::PARAM_STR);
				$fields[] = array('name' => 'onset', "value" => date('Y-m-d H:i:s',strtotime($this->onset)), "type" => PDO::PARAM_STR);
				$fields[] = array('name' => 'expires', "value" => date('Y-m-d H:i:s',strtotime($this->expires)), "type" => PDO::PARAM_STR);
				$fields[] = array('name' => 'status_id', "value" => $this->status, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'msgType_id', "value" => $this->msgType, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'source_id', "value" => $this->source, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'iicode', "value" => $this->iicode, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'scope_id', "value" => $this->scope, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'severity_id', "value" => $this->severity, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'responseType_id', "value" => $this->responseType, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'category_id', "value" => $this->category, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'urgency_id', "value" => $this->urgency, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'certainty_id', "value" => $this->certainty, "type" => PDO::PARAM_INT);
				if ($this->event != null){
					$fields[] = array('name' => 'event_id', "value" => $this->event, "type" => PDO::PARAM_INT);
				}
				if ($codeid != null){
					$fields[] = array('name' => 'code_id', "value" => $codeid, "type" => PDO::PARAM_INT);
				}
				
				$fields[] = array('name' => 'published', "value" => 1, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'license_id', "value" => $licenseid, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'areaColor_id', "value" => $areaColorId, "type" => PDO::PARAM_INT);
				$fields[] = array('name' => 'profileVersion_id', "value" => $profileVersionId, "type" => PDO::PARAM_INT);

				$insertid = horizonMySQL::insert('cap_data',$fields);

				if ($insertid){

					$fields = array();
					$fields[] = array('name' => 'cap_id', "value" => $insertid, "type" => PDO::PARAM_INT);

					if (!horizonMySQL::query('DELETE FROM cap_data_parameters WHERE cap_id = :cap_id',$fields)){
						die("Could not delete data\n");
					}
					if (!horizonMySQL::query('DELETE FROM cap_data_groups WHERE cap_id = :cap_id',$fields)){
						die("Could not delete data\n");
					}


					if (is_array($this->info)){

						$tmpgroups = dwdCapMessage::get('cap_types_group');

						foreach($this->info as $key => $val){


							foreach ($this->groups as $innerkey => $groupid){
								if (isset($tmpgroups[$groupid])){
									$sql = '';
									$fields = array();
									$fields[] = array('name' => 'cap_id', "value" => $insertid, "type" => PDO::PARAM_INT);
									$fields[] = array('name' => 'group_id', "value" => $tmpgroups[$groupid], "type" => PDO::PARAM_INT);
									$groupinsertid = horizonMySQL::insert('cap_data_groups',$fields);
								} else {
								}
							}

							if (!$val->toSQL($insertid)){
								throw new Exception('SQL Error',1002);
							}							
						}
					}

					if (is_array($this->parameter) && count($this->parameter) > 0){
						$currentParams = self::get('cap_data_parameters_type');
						$currentUnits = self::get('cap_data_parameters_units');
						foreach($this->parameter as $pname => $value){
							if (!isset($currentParams[$pname])){
								$fields = array();
								$fields[] = array('name' => 'name', "value" => $pname, "type" => PDO::PARAM_STR);
								$ptypeid = horizonMySQL::insert('cap_data_parameters_type',$fields);
								if ($ptypeid == 0){
									throw new Exception('Could not insert parameter type with name '.$type,1027);
								}
								$currentParams[$pname] = $ptypeid;
								self::set('cap_data_parameters_type',$currentParams);
							}
							foreach($value as $key => $data){
								if ($data['unit'] !== null ){
									if (!isset($currentUnits[$data['unit']])){
										$fields = array();
										$fields[] = array('name' => 'name', "value" => $data['unit'], "type" => PDO::PARAM_STR);
										$punitid = horizonMySQL::insert('cap_data_parameters_units',$fields);
										if ($punitid == 0){
											throw new Exception('Could not insert parameter unit',1028);
										}
										$currentUnits[$data['unit']] = $punitid;
										self::set('cap_data_parameters_units',$currentUnits);
									}
								}
								$fields = array();
								$fields[] = array('name' => 'cap_id', "value" => $insertid, "type" => PDO::PARAM_INT);
								$fields[] = array('name' => 'type_id', "value" => $currentParams[$pname], "type" => PDO::PARAM_INT);
								if ($data['unit'] !== null){
									$fields[] = array('name' => 'unit_id', "value" => $currentUnits[$data['unit']] , "type" => PDO::PARAM_INT);
								}
								$fields[] = array('name' => 'value', "value" => $data['value'] , "type" => PDO::PARAM_STR);
								$paramid = horizonMySQL::insert('cap_data_parameters',$fields);
								
								
							}
						}
					}
					
					if (is_array($this->area)){
						foreach($this->area as $key => $val){
							if (!$val->toSQL($insertid)){
								throw new Exception('SQL Error',1003);
							}
						}
					} else {
						die('doh');
					}

					if (is_array($this->references)){
						foreach($this->references as $key => $val){
							if (isset($val['identifier']['uuid'])){

								$reffields = array();
								$reffields[] = array('name' => 'cap_id', "value" => $insertid, "type" => PDO::PARAM_INT,'unique' => true);
								$reffields[] = array('name' => 'reference', "value" => $val['identifier']['uuid'], "type" => PDO::PARAM_STR,'unique' => true);

								$refinsertid = horizonMySQL::insert('cap_data_references',$reffields);

								if ($refinsertid){

									$fields = array();
									$fields[] = array('name' => 'guid', "value" => $val['identifier']['uuid'], "type" => PDO::PARAM_STR);
									$refid = horizonMySQL::query('UPDATE cap_data SET published = 0 WHERE guid = :guid',$fields);
									if ($refid !== false){
									} else {
										echo "Warning Msg ".$val['identifier']['uuid']." does not exist\n";
									}
									
								} else {
									throw new Exception('SQL Error',1007);
								}
							} else {
								throw new Exception('Invalid identifier',1008);
							}
						}
					}
					return true;
				} else {
					$tmp = print_r($this->db->errorInfo(),true);
					throw new Exception("SQL Error: ".$tmp,1013);
					
				}
			} elseif ($this->identifier['suffix'] == 'SPA' ||  $this->identifier['suffix'] == 'MUL' || $this->identifier['suffix'] == 'FRA' || $this->identifier['suffix'] == 'ENG') {
				if ($this->hasInfo()){
					$query = 'SELECT cap_id FROM cap_data WHERE guid = :guid';

					$params = array();
					$params[] = array('name' => 'guid', "value" => $this->identifier['uuid'], "type" => PDO::PARAM_STR);
					
					if (horizonMySQL::query($query,$params)){
						if($result = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
							if (isset($result['cap_id'])){
								$insertid = $result['cap_id'];
								if (is_array($this->info)){
									foreach($this->info as $key => $val){
										if (!$val->toSQL($insertid)){
											throw new Exception('SQL Error',1017);
											
										}
									}
									return true;
								}
							} else {
								throw new Exception('Capid is empty '.$query,1022);
							}
						} else {
							$tmp = print_r($params,true);
							throw new Exception('Could not determine guid '.$query." ".$tmp,1021);
						}	
					} else {
						throw new Exception('Query failed '.$query,1020);
					}	
				} else {
					throw new Exception('No info section present '.$this->identifier['suffix'],1018);
				}
			} else {
				throw new Exception('Unkown suffix '.$this->identifier['suffix'],1019);
			}
		} catch (Exception $e) {
			horizonException::handleException($e);
		}
	}

	static function clear_tables(){
		$query = array(
			"SET FOREIGN_KEY_CHECKS = 0; ",
			"TRUNCATE cap_data_parameters_type;",
			"TRUNCATE cap_data_parameters_units;",
			"TRUNCATE cap_data_polygons;",
			"TRUNCATE cap_data_areas;",
			"TRUNCATE cap_data_info;",
			"TRUNCATE cap_data_references;",
			"TRUNCATE cap_data_parameters;",
			"TRUNCATE cap_data_groups;",
			"TRUNCATE cap_data_sender;",
			"TRUNCATE cap_data_licenses;",
			"TRUNCATE cap_data_areaColors;",
			"TRUNCATE cap_data_profileVersions;",
			"TRUNCATE cap_data;",
			"SET FOREIGN_KEY_CHECKS = 1; ",
		);

		if (!horizonMySQL::query(join("\n",$query))){
			die("Could not delete data\n");
		}
	}

	static function &getInstance(){
		return self::$_instance;
	}
	
	static function set($key, $value){
		self::$_instance->$key = $value;
	}
	
	static function &get($key){
		return self::$_instance->$key;
	}
	
}


?>