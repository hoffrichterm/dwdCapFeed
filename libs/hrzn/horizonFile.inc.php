<?php

class horizonFile {

	static public function read($file){
		if (file_exists($file)){
			if (is_readable($file)){
				if (filesize($file) > 0){
					return file_get_contents($file);
				}
			}
		}
		return false;
	}

	static public function write($file,$str){
		$tmpfile = self::writeTempFile($str);
		if ($tmpfile){
			if (self::renameFile($tmpfile,$file)){
				return true;
			}
		}
		return false;
	}

	static public function chmod($file,$mode){
		$mode = intval($mode);
		if ($mode >= 0 && $mode <= 777){
			$mode = decoct($mode);
			if (file_exists($file)){
				if (is_writeable($file)){
					return chmod($file,$mode);
				}
			}
		}
		return false;
	}

	static public function writeTempFile($str){
		try {
			$suffix = 'horizonFile_';
			$tmpdir = sys_get_temp_dir();
			$temp_file = tempnam($tmpdir, $suffix);
			if ($temp_file != null){
				if (file_exists($temp_file)){
					if (is_writable($temp_file)){	
						if (file_put_contents($temp_file,$str)){
							return $temp_file;
						} else {
							throw new horizonFileException("Temp file ".$temp_file." is not writable\n",1008);
						}
					} else {
						throw new horizonFileException("Temp file ".$temp_file." is not writable\n",1007);
					}
				} else {
					throw new horizonFileException("Temp file ".$temp_file." not exists\n",1005);
				}
			} else {
				throw new horizonFileException("Temp file could not be created in tempdir ".$tmpdir."\n",1006);
			}
		} catch (Exception $e){
			horizonException::handleException($e);
		}
		return false;
	}

	static public function unlinkFile($file){
		if (file_exists($file)){
			if (is_writable($file)){
				if (@unlink($file)){
					return true;
				}
			}
		}
		return false;
	}

	static public function renameFile($src,$dest){
		if (file_exists($src)){
			if (is_writable($src)){
				if (is_writable(dirname($dest))){
					if (rename($src,$dest)){
						return true;
					} else {
						throw new horizonFileException("Could not rename file ".$src." ".$dest."\n",1001);
					}
				} else {
					throw new horizonFileException(dirname($dest)." is not writable\n",1002);
				}
			} else {
				throw new horizonFileException($src." is not writable\n",1003);
			}
		} else {
			throw new horizonFileException($src." does not exist\n",1004);
		}
		return false;
	}


}

?>