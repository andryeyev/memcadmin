<?php

class Memcadmin_Model_Node {

	private $_name = 'Node';
	private $_ip = '127.0.0.1';
	private $_port = 11211;
	private $_lastResponseTime;

	public function __construct($name = null) {

		if ($name)
			$this->setName($name);
	}

	public function __destruct() {}

	public function setName($name) {

		$this->_name = $name;
	}

	public function getName() {

		return $this->_name;
	}

	public function setIp($ip) {

		$this->_ip = $ip;
	}

	public function getIp() {

		return $this->_ip;
	}

	public function setPort($port) {

		$this->_port = $port;
	}

	public function getPort() {

		return $this->_port;
	}

	public function getUpState() {

		return Memcadmin_Memcache::testConnection($this->getIp(), $this->getPort());
	}

	public function getLastResponseTime() {

		return Memcadmin_Memcache::getLastResponseTime();
	}

	public function getFullStats() {

		$stats = array(
			'pid' => '-',
			'uptime' => '-',
			'time' => '-',
			'version' => '-',
			'pointer_size' => '-',
			'rusage_user' => '-',
			'rusage_system' => '-',
			'curr_items' => '-',
			'total_items' => '-',
			'bytes' => '-',
			'curr_connections' => '-',
			'total_connections' => '-',
			'connection_structures' => '-',
			'reserved_fds' => '-',
			'cmd_get' => '-',
			'cmd_set' => '-',
			'cmd_flush' => '-',
			'cmd_touch' => '-',
			'get_hits' => '-',
			'get_misses' => '-',
			'delete_misses' => '-',
			'delete_hits' => '-',
			'incr_misses' => '-',
			'incr_hits' => '-',
			'decr_misses' => '-',
			'decr_hits' => '-',
			'cas_misses' => '-',
			'cas_hits' => '-',
			'cas_badval' => '-',
			'touch_hits' => '-',
			'touch_misses' => '-',
			'auth_cmds' => '-',
			'auth_errors' => '-',
			'evictions' => '-',
			'reclaimed' => '-',
			'bytes_read' => '-',
			'bytes_written' => '-',
			'limit_maxbytes' => '-',
			'threads' => '-',
			'conn_yields' => '-',
			'hash_power_level' => '-',
			'hash_bytes' => '-',
			'hash_is_expanding' => '-',
			'expired_unfetched' => '-',
			'evicted_unfetched' => '-',
			'slab_reassign_running' => '-',
			'slabs_moved' => '-');

		$r = Memcadmin_Memcache::sendCommand($this->getIp(), $this->getPort(), 'stats');

		if (isset($r['STAT'])) {
			foreach($r['STAT'] as $k => $v) {
				$stats[$k] = $v;
			}
		}

		return $stats;
	}
}