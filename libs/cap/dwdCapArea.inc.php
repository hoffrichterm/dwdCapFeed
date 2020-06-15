<?php
class dwdCapArea extends dwdCapAreaBase {


	public function toSQL($capid){
		try {
			$fields = array();
			$altitude = $this->altitude;
			if ($this->altitude > 0){
				$fields[] = array('name' => 'altitude', "value" => (string) $this->altitude, "type" => PDO::PARAM_STR);
			}
			if ($this->ceiling != 9842.5197){
				$fields[] = array('name' => 'ceiling', "value" => (string) $this->ceiling, "type" => PDO::PARAM_STR);
			}
			$fields[] = array('name' => 'cap_id', "value" => $capid, "type" => PDO::PARAM_INT);

			if ($this->type == 'warncell') {
				$fields[] = array('name' => 'warncell', "value" => $this->warncells[0]['warncellId'], "type" => PDO::PARAM_INT,'unique' => true);
				$fields[] = array('name' => 'code', "value" => $this->warncells[0]['code'], "type" => PDO::PARAM_STR);
				$fields[] = array('name' => 'county', "value" => substr($this->warncells[0]['code'],0,5), "type" => PDO::PARAM_STR);
				$table = 'cap_data_areas';
			} elseif ($this->type == 'polygon') {
				$fields[] = array('name' => 'polygon', "value" => $this->polygon[0], "type" => PDO::PARAM_STR);
				$fields[] = array('name' => 'type', "value" => 'include', "type" => PDO::PARAM_STR);

				$table = 'cap_data_polygons';
			} elseif ($this->type == 'excludepolygon') {
				$fields[] = array('name' => 'polygon', "value" => $this->polygon[0], "type" => PDO::PARAM_STR);
				$fields[] = array('name' => 'type', "value" => 'exclude', "type" => PDO::PARAM_STR);
				$table = 'cap_data_polygons';
			}

			$insertid = horizonMySQL::insert($table,$fields);

			if ($insertid !== false){
				return true;
			}
			echo "nope ".$insertid;
			return false;

		} catch (PDOException $e) {
			$stmt = null;
			$this->db = null;
		  die('Connection failed: ' . $e->getMessage());
		}
		
	}


}

?>