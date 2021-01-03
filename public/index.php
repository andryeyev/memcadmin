<?php

defined('DOCUMENT_ROOT')
	|| define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

defined('APPLICATION_PATH')
	|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../src'));

defined('APPLICATION_CONFIG_PATH')
	|| define('APPLICATION_CONFIG_PATH', realpath(dirname(__FILE__) . '/../config'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH),
    get_include_path(),
)));

require_once 'Memcadmin.php';
$application = new Memcadmin_Application(APPLICATION_CONFIG_PATH.'/config.ini');
$application->init()->run();