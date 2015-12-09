<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));
defined('APPLICATION_PUBLIC_PATH') || define('APPLICATION_PUBLIC_PATH', realpath(dirname(__FILE__)));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

defined('WEBROOT') || define('WEBROOT', 'http://'.$_SERVER['HTTP_HOST'].'/money/');
define('WEB_BASE_URL', 'http://'.$_SERVER['HTTP_HOST']);

defined('WEBURL') || define('WEBURL', WEB_BASE_URL.WEBROOT);

defined('TPL_PATH') || define('TPL_PATH', APPLICATION_PUBLIC_PATH.'/tpl');
set_include_path(implode(PATH_SEPARATOR, array(
	realpath(APPLICATION_PUBLIC_PATH . '/library'),
	realpath(APPLICATION_PUBLIC_PATH . '/library/App/lib'),
	get_include_path(),
)));
require_once 'Zend/Application.php';
$application = new Zend_Application(
	APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()->run();
