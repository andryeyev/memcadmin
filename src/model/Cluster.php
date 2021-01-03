<?php

class Memcadmin_Model_Cluster {

	private $_name = 'Cluster';
	private $_nodes = null;

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

	public function addNode($node) {

		if ($node)
			$this->_nodes[] = $node;
	}

	public function getNodes() {

		return $this->_nodes;
	}
}