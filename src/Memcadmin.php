<?php

require_once 'Misc.php';
require_once 'Memcache.php';
require_once 'Controller.php';
require_once 'model/Cluster.php';
require_once 'model/Node.php';

class Memcadmin_Application {

	private $_config = null;
	private $_structure = null;
	private $_routeDefault = 'overview';
	private $_controller = null;

	public function __construct($configFilename = null) {

		$this->_settings();

		ob_start();

		$this->_config = $this->_readConfig($configFilename);

		if ($this->_config)
			return true;

		return false;
	}

	public function __destruct() {

		ob_end_flush();
	}

	private function _settings() {

		ini_set('display_errors', 1);
		error_reporting(E_ALL);
		ini_set("default_charset", "UTF-8");

		ini_set("mbstring.language", "Neutral");
		ini_set("mbstring.encoding_translation", "On");
		ini_set("mbstring.detect_order", "auto");
		ini_set("mbstring.substitute_character", "none");
		ini_set("mbstring.func_overload", 7);

		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	public function init() {

		if ($this->_config)
			$this->_create($this->_config);

		return $this;
	}

	public function route() {

		$rqStr = explode('/', $_SERVER["REQUEST_URI"]);
		$requestParams = array();
		$actionParam = null;		
		$called = false;

		if (isset($_GET['a']))
			$actionParam = $_GET['a'];
		
		$this->_controller = new Memcadmin_Controller($requestParams, $this->_structure);
		
		if ($actionParam) {
			$action = 'action'.ucwords(strtolower(trim($actionParam)));

			if(is_callable(array($this->_controller, $action))){
				$this->_controller->$action();
				if (!$this->_controller->isPlain())
					$this->_controller->meld(strtolower(trim($actionParam)));
				$called = true;
			}
		}

		if (!$called) {
			$action = 'action'.ucwords($this->_routeDefault);			
			$this->_controller->$action();
			if (!$this->_controller->isPlain())
				$this->_controller->meld($this->_routeDefault);
		}
	}

	public function run() {

		if ($this->_structure)
			$this->route();

		$ob_content = ob_get_contents();
		ob_clean();

		if (!$this->_controller || !$this->_controller->isPlain())
			include_once 'view/_layout.phtml';
		else
			echo $ob_content;

		return $this;
	}

	private function _create($config) {

		if (is_array($config) && !empty($config)) {

			$this->_structure = array();

			foreach($config as $cluster => $nodes) {

				$clusterModel = new Memcadmin_Model_Cluster($cluster);

				foreach($nodes as $node => $conf) {

					$nodeModel = new Memcadmin_Model_Node($node);

					if (isset($conf['ip']))
						$nodeModel->setIp($conf['ip']);

					if (isset($conf['port']))
						$nodeModel->setPort($conf['port']);

					$clusterModel->addNode($nodeModel);
				}

				$this->_structure[] = $clusterModel;
			}
		}

		$this->_structure;
	}

	private function _readConfig($filename = null) {

		$this->_config = array();

		if ($filename) {
			$configIn = parse_ini_file($filename, true);

			if (is_array($configIn) && !empty($configIn)) {

				foreach ($configIn as $name => $conf) {

					list($clusterName, $nodeName) = explode(':', $name);

					$clusterName = trim($clusterName);
					$nodeName = trim($nodeName);

					if (!isset($this->_config[$clusterName]))
						$this->_config[$clusterName] = array();

					if ($nodeName == '')
						$nodeName = $clusterName;

					$this->_config[$clusterName][$nodeName] = $conf;

				}
			}
		}

		return $this->_config;
	}
}
