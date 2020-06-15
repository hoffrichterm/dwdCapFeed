<?php

class horizonMySQL {

	protected static $_this = null;
	protected $dbh = null;
	protected $database = null;

	protected function __construct($dsn, $user, $password, $database, $dbhoptions){
		try {
			$this->dbh = new PDO($dsn, $user, $password, $dbhoptions);
			$this->database = $database;
		} catch (Exception $e) {
			horizonException::handleException($e);
		}
	}

	protected function __clone(){
	}

	function __destruct(){
		self::$_this->dbh = null;;
		self::$_this = null;;
	}

	public static function getInstance($options){
		try {
			if (!is_array($options)){
				throw new InvalidArgumentException("Options are not set",1000);
			}
			if (!isset($options['user'])){
				throw new InvalidArgumentException("User is not set",1001);
			}
			if (!isset($options['password'])){
				throw new InvalidArgumentException("Password is not set",1002);
			}
			if (!isset($options['database'])){
				throw new InvalidArgumentException("database is not set",1004);
			}
			if (self::$_this === null){
				$dsn = 'mysql:host='.(isset($options['host']) ? $options['host'] : 'localhost').';dbname='.$options['database'];
				self::$_this = new self($dsn,$options['user'],$options['password'],$options['database'],$options['options']);
			}
			return self::$_this;
		} catch (Exception $e) {
			horizonException::handleException($e);
		}
	}

/*
	public static function set($key, $value){
		self::$_this->$key = $value;
	}

	public static function &get($key){
		return self::$_this->$key;
	}
*/
	public static function query($sql,$params = null,$options = null){
		if (self::$_this !== null){
			try{
				self::$_this->stmt = null;
				$stmt = self::$_this->prepare($sql,$options);
				if (is_array($params)){
					if (is_array($params[0])){
						foreach($params as $key => $param){
							$stmt->bindParam($param['name'], $param['value'], $param['type']);
						}
						$ret = $stmt->execute();
					} else {
						$ret = $stmt->execute($params);
					}
				} else {
					$ret = $stmt->execute();
				}
				self::$_this->stmt = $stmt;
				return $ret;
			} catch (Exception $e) {
				$mysqlException = new horizonMySQLException('Invalid SQL',1000,$e);
				if ($params != null){
					$mysqlException->setParams($params);
				}
				horizonException::handleException($mysqlException);
			}
		}
		return false;
	}
	
	public static function fetch($type = null){
		$retVal = false;
		if (self::$_this !== null){
			$stmt = self::$_this->stmt;
			if ($type == null){
				$retVal = $stmt->fetch();
				if ($retVal){
					return $retVal;
				} else {
					return false;
				}
			} else {
				$retVal = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($retVal){
					return $retVal;
				} else {
					return false;
				}
			}
		}
		return false;
	}
	
	protected function prepare($sql,$options = null){
		if ($options !== null){
			$stmt = self::$_this->dbh->prepare($sql,$options);
		} else {
			$stmt = self::$_this->dbh->prepare($sql);
		}
		return $stmt;
	}
	
	public static function transactionStart(){
		if (self::$_this !== null){
			self::$_this->dbh->beginTransaction();
		}
	}
	
	public static function commit(){
		if (self::$_this !== null){
			self::$_this->dbh->commit();
		}
	}
	
	public static function rollback(){
		if (self::$_this !== null){
			self::$_this->dbh->rollBack();
		}
	}

	public static function getPrimaryKey ($table){
		if (self::$_this !== null){
			$stmt = null;
			$sql = 'show columns from `'.self::$_this->database.'`.`'.$table.'` where `Key` = "PRI"';
			$stmt = self::$_this->dbh->prepare($sql);
			$exec = $stmt->execute();
			if ($exec !== false){
				if($result = $stmt->fetch(PDO::FETCH_ASSOC)){
					if (isset($result['Field'])){
						$stmt = null;
						return $result['Field'];
					}
				}
			}
			
		}
		return false;
		
	}
	public static function insert($table,$fields){
		$uniquecounter = null;
		if (self::$_this !== null){
			$stmt = null;
			#$sql = 'SELECT * FROM '.$table;
			#self::query($sql);
			$sql = 'INSERT INTO '.$table.' (';
			$tmp = array();
			$uniquefields = array();
			for ($fieldcounter = 0; $fieldcounter < count($fields);$fieldcounter++){
				$tmp[] = $fields[$fieldcounter]['name'];
				if (isset($fields[$fieldcounter]['unique']) && $fields[$fieldcounter]['unique'] == true){
					$uniquefields[] = $fields[$fieldcounter];
					$uniquecounter = $fieldcounter;
				}
			}
			$sql .= join(', ',$tmp);
			$sql .= ")\nVALUES (";
			$tmp = array();
			for ($fieldcounter = 0; $fieldcounter < count($fields);$fieldcounter++){
				$tmp[] = ':'.$fields[$fieldcounter]['name'];
			}
			$sql .= join(', ',$tmp);
			$sql .= ')';
			if (count($uniquefields) > 0 ){
				$sql .= " ON DUPLICATE KEY UPDATE \n";
				$tmp = array();
				for ($fieldcounter = 0; $fieldcounter < count($fields);$fieldcounter++){
					$tmp[] = "\t".$fields[$fieldcounter]['name'].' = :'.$fields[$fieldcounter]['name'];
				}
				$sql .= join(",\n",$tmp);
				$sql .= ';';
			}
			$stmt = self::$_this->dbh->prepare($sql);
			for ($fieldcounter = 0; $fieldcounter < count($fields);$fieldcounter++){
				if (isset($fields[$fieldcounter]) && is_array($fields[$fieldcounter])){
					if (!is_scalar($fields[$fieldcounter]['name']) || !is_scalar($fields[$fieldcounter]['value']) || !is_scalar($fields[$fieldcounter]['type'])){
						$str = '';
						$str .= print_R($sql	,true)."\n";
						$str .= print_R($fields	,true)."\n";
						throw new Exception('invalid field: '.$str,1005);
					} else {
						$stmt->bindParam(":".$fields[$fieldcounter]['name'], $fields[$fieldcounter]['value'], $fields[$fieldcounter]['type']);
					}
				} else {
					throw new Exception('invalid fields',1006);
				}
			}
			$exec = $stmt->execute();
			if ($exec === false){
				$str = '';
				$str .= print_R($fields,true)."\n";
				$str .= print_R($table,true)."\n";
				$str .= print_R($stmt,true)."\n";
				$str .= print_R(self::$_this->dbh,true)."\n";
				$str .= print_R(self::$_this->dbh->errorInfo(),true)."\n";
				$str .= print_R(self::$_this->dbh->errorCode(),true)."\n";
				$stmt = null;
				throw new Exception('SQL Error - Cannot execute query '.$str,1006);
			}
			$stmt = null;


			$insertid = intval(self::$_this->dbh->lastInsertId());

			if ($insertid == 0){
				if (count($uniquefields) > 0 ){
					$primary = self::getPrimaryKey($table);
					if ($primary){
						$stmt = self::$_this->dbh->prepare('SELECT '.$primary.' FROM '.$table.' WHERE '.$fields[$uniquecounter]['name'].' = :key');
						$stmt->bindParam(":key", $fields[$uniquecounter]['value'], $fields[$uniquecounter]['type']);
						if ($stmt->execute()!== false){
							if($result = $stmt->fetch(PDO::FETCH_ASSOC)){
								if (isset($result[$primary])){
									$stmt = null;
									return $result[$primary];
								}
							}
				
						}
					} else {
						throw new Exception('SQL Error - Table '.$table.' has no primary key ',1006);
					}
				} else {
					$stmt = null;
					return false;
				}
			} else {
				$stmt = null;
				return $insertid;
			}
			$stmt = null;
		}
		return false;
	}
}

?>