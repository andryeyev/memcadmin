<?php

abstract class Memcadmin_Memcache {

	static private $_lastResponseTime;

	public static function getLastResponseTime() {

		return self::$_lastResponseTime;
	}

	public static function testConnection($server, $port) {

		$startTime = Memcadmin_Misc::getMicrotimeFloat();
		$s = @fsockopen($server,$port, $errno, $errstr, 3); // wait max. 3 seconds
		self::$_lastResponseTime = Memcadmin_Misc::getMicrotimeFloat()-$startTime;

		if (!$s)
			return false;

		return true;
	}

	public static function sendCommand($server, $port, $command) {

		$s = @fsockopen($server,$port);
		if (!$s)
			return '';

		fwrite($s, $command."\r\n");

		$buf='';
		while ((!feof($s))) {
			$buf .= fgets($s, 256);
			if (strpos($buf,"END\r\n")!==false){ // stat says end
				break;
			}
			if (strpos($buf,"DELETED\r\n")!==false || strpos($buf,"NOT_FOUND\r\n")!==false){ // delete says these
				break;
			}
			if (strpos($buf,"OK\r\n")!==false){ // flush_all says ok
				break;
			}
		}
		fclose($s);

		return self::_parseMemcacheResults($buf);
	}

	private static function _parseMemcacheResults($str) {
		
		$res = array();
		$lines = explode("\r\n",$str);
		$cnt = count($lines);
		for($i=0; $i< $cnt; $i++){
			$line = $lines[$i];
			$l = explode(' ',$line,3);
			if (count($l)==3){
				$res[$l[0]][$l[1]]=$l[2];
				if ($l[0]=='VALUE'){ // next line is the value
					$res[$l[0]][$l[1]] = array();
					list ($flag,$size)=explode(' ',$l[2]);
					$res[$l[0]][$l[1]]['stat']=array('flag'=>$flag,'size'=>$size);
					$res[$l[0]][$l[1]]['value']=$lines[++$i];
				}
			}elseif($line=='DELETED' || $line=='NOT_FOUND' || $line=='OK'){
				return $line;
			}
		}
		return $res;

	}

	public static function getSlabs($server, $port) {
		$itemlist = self::sendCommand($server, $port, 'stats items');
		$serverItems = array();
		$totalItems = 0;

		if (isset($itemlist['STAT'])) {
			$iteminfo = $itemlist['STAT'];

			foreach($iteminfo as $keyinfo => $value){
				if (preg_match('/items\:(\d+?)\:(.+?)$/', $keyinfo,$matches)){
					$serverItems[$matches[1]][$matches[2]] = $value;
					if ($matches[2] == 'number'){
						$totalItems += $value;
					}
				}
			}
		}

		return array('items' => $serverItems, 'count' => $totalItems);
	}

	public static function getSlabDump($server, $port, $slabId, $limit = 0) {

		$r = self::sendCommand($server, $port, 'stats cachedump '.$slabId.' '.$limit);

		if (isset($r['ITEM']))
			return $r['ITEM'];
		else
			return array();
	}

	public static function getKey($server, $port, $key) {

		$r = self::sendCommand($server, $port, 'get '.$key);

		if (isset($r['VALUE'][$key]))
			return $r['VALUE'][$key];
		else
			return null;
	}

	public static function flush($server, $port) {

		return self::sendCommand($server, $port, 'flush_all');
	}
}