<?php
class Proccess_Worker
{
	private $__event = NULL;
	private $__protocol = NULL;
	
	public function __construct()
	{
		$this->__event = new Event_Event();
	}
	
	public function task_worker()
	{
		$context = new ZMQContext();
		$worker = new ZMQSocket($context, ZMQ::SOCKET_DEALER);
		$worker->connect('ipc://backend');
		$fd_worker = $worker->getSockOpt(ZMQ::SOCKOPT_FD);
		
		$this->__event->add($fd_worker, EV_READ | EV_PERSIST, array($this, 'deal_data'), $worker);
		$this->__event->loop();
	}
	
	
	public function deal_data($fd, $event, $args)
	{
		echo 'CALLBACK FIRE3' . PHP_EOL;
		if($args->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN)
		{
			$content = $args->recvMulti();
			var_dump($content);
			$args->sendMulti($content);
		}
	}
}