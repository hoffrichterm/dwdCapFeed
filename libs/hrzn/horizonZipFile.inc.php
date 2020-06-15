<?php

class horizonZipFile {

	public static function Unzip($zipFile,$tempdir) {
		if (is_file($zipFile) && is_readable($zipFile)){
			$zip = new ZipArchive;
			$result = $zip->open($zipFile);
			if($result !== true){
				echo "Error :- Unable to open the Zip File: $result";
				return false;
			} 
			$zip->extractTo($tempdir);
			$zip->close();
			return true;
		}
	}
}

?>