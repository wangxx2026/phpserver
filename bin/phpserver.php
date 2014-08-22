<?php
define('APP_PATH', realpath(__DIR__) . '/');
define('ROOT_PATH', APP_PATH .'../');

var_dump($argv);
die();

require ROOT_PATH . 'lib/proc/master.php';
require ROOT_PATH . 'lib/server/server.php';

$master = new master();

$server = new lib_server('127.0.0.1', '7001');
