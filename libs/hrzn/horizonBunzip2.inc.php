<?php

class horizonBunzip2 {
	
	public static function Unzip($zipFile) {
		if (is_file($zipFile) && is_readable($zipFile)){
			$bz = bzopen($file, "r");
			if ($bz){
				$retVal = '';
				while (!feof($bz)) {
				  $retVal .= bzread($bz, 4096);
				}
				bzclose($bz);
				if ($retVal != ''){
					return $retVal;
				} else {
					throw new Exception('File not readable',1001);
				}
			} else {
				throw new Exception('File not readable',1000);
			}
		} else {
			throw new Exception('File not readable',1002);
		}
		return false;
	}

	public static function Decompress($str,$small) {
		return bzdecompress($str,$small);
	}
	
	
}
?>