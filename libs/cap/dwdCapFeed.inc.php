<?php


class dwdCapFeed {
	const CONTENTLOG = 'https://opendata.dwd.de/weather/alerts/content.log.bz2';

	protected $importdir = null;
	protected $historyfile = null;
	protected $history = array();
	protected $lastrun = null;

	private $files = array();

	function __construct($importdir){

		spl_autoload_register(function ($class) {
			$cwd = dirname(__FILE__);
			$classfile = $cwd.DIRECTORY_SEPARATOR.$class.'.inc.php';
			if (file_exists($classfile)){
				require($classfile);
			}
		});

		# Trying to create importdir if not exists
		if (!is_dir($importdir)){
			if (!@mkdir($importdir,700,true)){
				throw new Exception('Cannot create importdir: '.$importdir,1000);
			}
		}
		if (!is_writeable($importdir)){
			throw new Exception('Cannot write to importdir: '.$importdir,1001);
		}
		$this->importdir = $importdir;
		# Trying to create historyfile if not exists
		$this->historyfile = $importdir.DIRECTORY_SEPARATOR.'.history';
		if (!file_exists($this->historyfile)){
			if (!file_put_contents($this->historyfile,serialize(array('history' => $this->history,'lastrun' => time())))){
				throw new Exception('Cannot create historyfile: '.$this->historyfile,1002);
			}
		} else {
			$str = file_get_contents($this->historyfile);
			$tmp = unserialize($str);
			$this->history = $tmp['history'];
			$this->lastrun = $tmp['lastrun'];
		}
	}

	function __destruct(){
		$this->writeHistory();
	}

	public function import(){
		$files = 0;
		if ($this->download()){
			$files = $this->getDownloadedFiles();
			foreach($files as $key =>$val){
				$obj = simplexml_load_file($val);
				if (is_object($obj)){

					$clsCap = new dwdCapMessage($obj);
					horizonMySQL::transactionStart();
					if ($clsCap->toSQL()){
						if(horizonFile::unlinkFile($val)){
							horizonMySQL::commit();
							$files++;
						} else {
							throw new Exception("Could not unlink file ".$val,1001);
							horizonMySQL::rollback();
						}
					} else {
						throw new Exception("SQL Failed ".$val,1001);
						horizonMySQL::rollback();
					}

				} else {
					throw new Exception("XML Read failed ".$val,1001);
				}
			}
			return $files;
		} else {
			throw new Exception("Could not download data from Opendata Server",1001);
		}
	}
	
	private function download(){
		$updatelastrun = false;
		if ($this->getFileList()){
			$clsUrl = new horizonUrl();
			foreach($this->files as $filename => $fileinfo){
				if (!isset($this->history[$filename])){
					try {
						$ret = $clsUrl->get($filename);
						if ($ret){
							$tmpfile = horizonFile::writeTempFile($clsUrl->getBody());
							if ($tmpfile){
								if(horizonZipFile::Unzip($tmpfile,$this->importdir)){
									if (horizonFile::unlinkFile($tmpfile)){
										$this->history[$filename] = $fileinfo;
										$updatelastrun = true;
									}
								} else {
									return false;
								}
							} else {
								return false;
							}
						} else {
							return false;
						}
					} catch (Exception $e) {
						echo "Exception found:\n";
						echo $e->getCode()."\n";
						echo $e->getMessage()."\n";
						return false;
					} 
				}
			}
			$clsUrl = null;
			if ($updatelastrun) $this->lastrun = time();
			if ($this->writeHistory()){
				return true;
			}
		}
		return false;
	}
	
	protected function getFileList(){
		try {
			$url = $this::CONTENTLOG;
			$tmp = horizonUrl::parseUrl($url);
			$base = $tmp['scheme'].'://'.$tmp['host'].$tmp['dirname'];
			$clsUrl = new horizonUrl();
			$ret = $clsUrl->get($url);
			if ($ret){
				$str = horizonBunzip2::Decompress($clsUrl->getBody(),true);
				$clsUrl = null;
				if ($str){
					$separator = "\n";
					$line = strtok($str, $separator);
					while ($line !== false) {
						# do something with $line
						$tmpline = $line;
						$line = strtok( $separator );
						$tmp = explode("|",$tmpline);
						if ($tmp[1] == 22) {
							continue;
						}
						if (preg_match("!^(\.)(/cap/COMMUNEUNION_EVENT_DIFF/Z_CAP_C_EDZW_.*)$!",$tmp[0],$regs)){
							$this->files[$base.$regs[2]] = array('filesize' => $tmp[1],'date' => strtotime($tmp[2]));
						} else {
							continue;
						}
					}
					return true;
				} else {
					throw new Exception('Content.log is empty ',1004);
				}
			} else {
				$clsUrl = null;
				throw new Exception('Cannot get url '.$url,1005);
			}
			$clsUrl = null;
			return false;
		} catch (Exception $e){
			echo "Exception: ".$e->getCode()."\n";
			echo "Message: ".$e->getMessage()."\n";
		}
	}
	
	protected function writeHistory(){
		if (file_exists($this->historyfile) && is_writable($this->historyfile)){
			if (file_put_contents($this->historyfile,serialize(array('history' => $this->history, 'lastrun' => $this->lastrun)))){
				return true;
			}
		}
		throw new Exception('Cannot create historyfile: '.$this->historyfile,1003);
	}

	public function getDownloadedFiles(){
		$files = glob($this->importdir.'/*.xml');
		usort($files, array($this, "sortFiles"));
		return $files;
	}

	protected function sortFiles($a, $b){
		$tmpa = explode(".",basename($a));
		$tmpb = explode(".",basename($b));
		$langa = $tmpa[10];
		$langb = $tmpb[10];
		$uida = $tmpa[9];
		$uidb = $tmpb[9];
		$tsa = $tmpa[8];
		$tsb = $tmpb[8];
		if ($tsa == $tsb) {
			if ($uida == $uidb) {
				if ($langa == $langb) {
					return 0;
				}
				return $langa == 'DEU' ? -1 : 1;
			}
			return $uida < $uidb ? -1 : 1;
		}
		return ($tsa < $tsb) ? -1 : 1;
	}

	public function getNextDate(){
		$sql = 'SELECT MIN(expires) as nextdate, COUNT(*) as anzahl FROM cap_data WHERE published = 1 AND expires > NOW();';
		if (horizonMySQL::query($sql)){
			while($row = horizonMySQL::fetch(PDO::FETCH_ASSOC)){
				if (isset($row['nextdate']) && isset($row['anzahl']) && $row['anzahl'] > 0){
					$next = strtotime($row['nextdate']);
					return $next;
				} elseif (isset($row['anzahl']) && $row['anzahl'] == 0){
					return 0;
				}
			}
		}
		return false;
	}
	
}

?>