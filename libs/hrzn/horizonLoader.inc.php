<?php


class horizonLoader {

	protected $includePath;
	
	public function __construct() {
		$classdir = dirname(__FILE__);
		if (isset($classdir) && is_dir($classdir) && is_readable($classdir)){
			$this->includePath = get_include_path();
			set_include_path($this->includePath.PATH_SEPARATOR.$classdir);
			spl_autoload_register(array($this,'autoload'));
			set_exception_handler(array('horizonException','handleException'));
			#set_error_handler(array('horizonException','handleError'));
			error_reporting(E_ALL);
		}
	}
		
	private function autoload($class){
		$classdir = dirname(__FILE__);
		switch($class){
			default :
				if (file_exists($classdir.DIRECTORY_SEPARATOR.$class.'.inc.php')){
					require($classdir.DIRECTORY_SEPARATOR.$class.'.inc.php');
				}
			break;
			
		}
	}
	
	function __destruct(){
		set_include_path($this->includePath);
	}

}

?>