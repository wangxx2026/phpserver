<?php 

class server
{

	private $__event_base           = NULL;

    public static $content_accept   = array();

    public static $content_worker   = array();

    public static $content_send     = array();
	
	public function __construct($host)
	{
		$this->__event_base = event_base_new();
		$this->server_task($host);
	}
	
	public function server_task($host)
	{
		$pid = pcntl_fork();
		if($pid == 0)
		{
			$this->server_worker();
			exit();
		}
		
		
		$context = new ZMQContext();
		$frontend = new ZMQSocket($context, ZMQ::SOCKET_ROUTER);
		$frontend->bind($host);
		$fd_frontend = $frontend->getSockOpt(ZMQ::SOCKOPT_FD);
		
		$backend = new ZMQSocket($context, ZMQ::SOCKET_DEALER);
		$backend->bind("ipc://backend");
		$fd_backend = $backend->getSockOpt(ZMQ::SOCKOPT_FD);

		$event = event_new();
		event_set($event, $fd_frontend, EV_READ | EV_PERSIST, array($this, 'accept'), array($frontend, $backend));
		event_base_set($event, $this->__event_base);
		event_add($event);

		$event2 = event_new();
		event_set($event2, $fd_backend, EV_READ | EV_PERSIST, array($this, 'send_msg'), array($backend, $frontend));
		event_base_set($event2, $this->__event_base);
		event_add($event2);
		
		
		event_base_loop($this->__event_base);
		exit('exit...');
		
	}
	
	public function accept($fd, $event, $args)
	{
		echo 'CALLBACK FIRED' . PHP_EOL;
		
		if($args[0]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN)
		{
			$content = $args[0]->recvMulti();
            var_dump($content);
            var_dump($args[1]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_OUT);
            $args[1]->sendMulti($content);
            //self::$content_accept[$content[0]] = $content;
		}


	}
	
	public function send_msg($fd, $event, $arg)
	{
		echo 'CALLBACK FIRED2' . PHP_EOL;
		if($arg[0]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN)
		{
			$content = $arg[0]->recvMulti();
			$content[2] .= '2';
            var_dump($content);
            //var_dump($arg[1]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_OUT);
            $arg[1]->sendMulti($content);
            var_dump($arg[0]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN);
            //var_dump($arg[1]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN);

            //self::$content_send[$content[0]] = $content;
		}

	}
	
	public function server_worker()
	{
		
		$context = new ZMQContext();
		$worker = new ZMQSocket($context, ZMQ::SOCKET_DEALER);
		$worker->connect('ipc://backend');
		$fd_worker = $worker->getSockOpt(ZMQ::SOCKOPT_FD);

		$event_base = event_base_new();
		$event = event_new();
		event_set($event, $fd_worker, EV_READ | EV_PERSIST, array($this, 'worker_deal'), $worker);
		event_base_set($event, $event_base);
		event_add($event);
		
		event_base_loop($event_base);
	}
	
	public function worker_deal($fd, $event, $args)
	{
		echo 'CALLBACK FIRED3' . PHP_EOL;
		if($args->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN)
		{
			$content = $args->recvMulti();;
            $content[2] .= '3';
            var_dump($content);

            var_dump($args->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_OUT);
            $args->sendMulti($content);

		}
       
	}
}


new server('tcp://127.0.0.1:7001');

?>
