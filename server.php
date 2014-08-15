<?php 
/**
 * phpserver
 * @date 2014-08-15 09:37:00
 * @author xingxingwnag
 */

class zmqServer
{
	
	private $__host;
	private $__port;
	private $__base;
	/**
	 * 构造方法
	 * @author xingxingwang
	 * @date 2014-08-15 09:57:00 	
	 */
	public function __construct($host, $port)
	{		
		$this->__host = $host;
		$this->__port = $port;
		$this->__base = event_base_new();
	}
	
	/**
	 * 
	 */
	public function server()
	{
		$context = new ZMQContext();
		$server = $context->getSocket(ZMQ::SOCKET_REP);
		
		
		$server->bind('tcp://'. $this->__host . ':' . $this->__port);
		
		$fd = $server->getSockOpt(ZMQ::SOCKOPT_FD);
		
		$event = event_new();
		
		event_set($event, $fd, EV_READ | EV_PERSIST, array($this, 'accept'), $server);
		
		event_base_set($event, $this->__base);
		event_add($event);
		
		event_base_loop($this->__base);
		
		echo '123';
		
	}
		
	public function accept($fd, $events, $arg)
	{
		static $msgs = 1;
		echo "CALLBACK FIRED", PHP_EOL;
		if($arg->getSockOpt(ZMQ::SOCKOPT_EVENTS))
		{
			var_dump($arg->recv());
			
			$arg->send("Got msg $msgs");
			$msgs++;
		}
	}	
	
}

$server = new zmqServer('127.0.0.1', 7001);
$server->server();
