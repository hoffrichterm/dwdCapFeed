<?php
class dwdCapInfo extends dwdCapInfoBase {

	private $db;

	public function toSQL($capid){

		try {
			
			
			$fields = array();
			$fields[] = array('name' => 'cap_id', "value" => $capid, "type" => PDO::PARAM_INT);
			$fields[] = array('name' => 'language', "value" => $this->language, "type" => PDO::PARAM_STR);

			if (!horizonMySQL::query('DELETE FROM cap_data_info WHERE cap_id = :cap_id AND language = :language',$fields)){
				die("Could not delete data\n");
			}

			$fields = array();
			$fields[] = array('name' => 'cap_id', "value" => $capid, "type" => PDO::PARAM_INT);
			$fields[] = array('name' => 'headline', "value" => $this->headline, "type" => PDO::PARAM_STR);
			$fields[] = array('name' => 'description', "value" => (string) $this->description, "type" => PDO::PARAM_STR);
			if ($this->instruction != null){
				$fields[] = array('name' => 'instruction', "value" => $this->instruction, "type" => PDO::PARAM_STR);
			}
			$fields[] = array('name' => 'language', "value" => $this->language, "type" => PDO::PARAM_STR);

			$insertid = horizonMySQL::insert('cap_data_info',$fields);

			if ($insertid !== false){
				return true;
			}
			return false;
		} catch (Exception $e) {
			$stmt = null;
			$this->db = null;
		  die('Connection failed: ' . $e->getMessage());
		  
		}
		
	}
	
	public function getGroups(){
		return $this->groups;
	}

	
}

?>