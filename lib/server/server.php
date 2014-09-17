<?php

class lib_server
{
	public function __construct($host, $port, $server = 'zmq')
	{
		require ROOT_PATH . 'lib/server/' . $server . '.php';
		
		new zmq_server($host, $port);
	}
}