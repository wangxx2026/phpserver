<?php
define('APP_PATH', realpath(__DIR__) . '/');
define('ROOT_PATH', APP_PATH .'../');

define('LIB_PATH', ROOT_PATH . 'lib/');

require LIB_PATH . 'Master.php';

$obj = new Master();
$obj->run('tcp://127.0.0.1:7001');
