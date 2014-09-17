<?php 
/**
 * phpserver
 * @date 2014-08-15 09:37:00
 * @author xingxingwnag
 */

require ROOT_PATH . 'lib/server/abstract.php';
class zmq_server extends server_abstract
{
	
	private $__host;
	private $__port;
	private $__base;
	private $__pre_proc = 'phpserver';
	
	private $__backend = null;
	
	private $__process_num = 1;
	
	private $__request_max = 1000;
	
	private $__protocol = null;
	/**
	 * 构造方法
	 * @author xingxingwang
	 * @date 2014-08-15 09:57:00 	
	 */
	public function __construct($host, $port, $protocol = 'aps')
	{		
		$this->__host = $host;
		$this->__port = $port;
		
		
		$this->__protocol = $protocol;
		
		$this->__protocol = $this->protocol($protocol);
		
		$this->server();
	}
	
	/**
	 * 
	 */
	public function server()
	{
		$context = new ZMQContext();
		
		// 前端
		$fronted = $context->getSocket(ZMQ::SOCKET_ROUTER);
		$fronted->bind('tcp://'. $this->__host . ':' . $this->__port);
		$fd = $fronted->getSockOpt(ZMQ::SOCKOPT_FD);
		
		// 后端
		$backend = $context->getSocket(ZMQ::SOCKET_DEALER);
		$backend->bind("inproc://backend");
		$fd_back = $backend->getSockOpt(ZMQ::SOCKOPT_FD);
		
		$this->__backend = $backend;
        
        $stream_info = stream_get_meta_data($fd);
		$this->__pre_proc .= substr($stream_info['stream_type'], 0, 3);
		
		$this->set_process_name('master');
		
		/*for($i = 0; $i < $this->__process_num; ++$i)
		{
			$this->spawnworker();
		}*/
		
		$this->__base = event_base_new();
		
		// 监听前端事件
		$event = event_new();
		event_set($event, $fd, EV_READ | EV_PERSIST, array($this, 'accept'), array($fronted, $backend));
		event_base_set($event, $this->__base);
		event_add($event);
		
		// 监听后端时间
		$event2 = event_new();
		event_set($event2, $fd_back, EV_READ | EV_PERSIST, array($this, 'accept2'), $backend);
		event_base_set($event2, $this->__base);
		event_add($event2);
		
		event_base_loop($this->__base);
		
		echo '服务已关闭', PHP_EOL;
		exit();
		
	}
	
	public function accept($fd, $event, $arg)
	{
		if($arg[0]->getSockOpt(ZMQ::SOCKOPT_EVENTS))
		{
			$content = $this->deal_data($arg[0]);
			echo "\n++++++++++++++++++++++++++++++++++\n";
			var_dump($content);
			echo "\n++++++++++++++++++++++++++++++++++\n";
			//var_dump($arg[1]);
			
			$context = new ZMQContext();
			
			$backend = $context->getSocket(ZMQ::SOCKET_DEALER);
			
			$backend->connect("inproc://backend");
			$res = $backend->sendMulti($content);
			var_dump($res);
		}
	}
	
	/**
	 * 处理接收的数据
	 * @param unknown_type $fd
	 * @param unknown_type $events
	 * @param unknown_type $arg
	 * @todo 事件处理完成删除事件
	 */
	public function accept2($fd, $events, $arg)
	{
		
		if($arg->getSockOpt(ZMQ::SOCKOPT_EVENTS))
		{
			$content = $this->recv($args);
			echo "\n------------------------------------------\n";
			var_dump($content);
			echo "\n------------------------------------------\n";
		}
	}

	/**
	 * 数据处理
	 * @param unknown_type $server
	 */
	/*public function deal_data($server)
	{
		// 数据接收 处理协议
		$content = $this->recv($server);
		// 数据发送
		$server->send("Got msg 123");
	}*/
	/**
	 * 接收数据
	 * @param unknown_type $server
	 */
	public function deal_data($server)
	{
		// 接收内容
		$content = $this->recv($server);
		//缓存
		$send_content = $this->cache($content);
		//处理协议
		if(!$send_content)
		{
			//$res = $this->__protocol->dealrecv($content);
			
			//$send_content = $this->__protocol->dealreply();
			
			//$this->send($this->__backend, $content);
			
		}
		
		//$this->send($this->__backend, $content);
		return $content;
		// 发送内容
		//$this->send($server, $send_content);
		
		
	}
	
	public function recv($server)
	{
		$content = $server->recvMulti();
		return $content;
	}
	public function send($server, $content)
	{
		
		return $server->sendMulti($content);
	}
	
	public function cache($content)
	{
		return false;
	}
	
	public function spawnworker()
	{
		$pid = pcntl_fork();
		
		if($pid > 0)
		{
			return;
		}
		$this->set_process_name('worker');
	}
	
	public function set_process_name($title)
	{
		if (function_exists('cli_set_process_title'))
		{
			@cli_set_process_title($this->__pre_proc . ':' . $title);
		}
	}
	
	/**
	 * 
	 * @param unknown $protocol
	 * @return unknown
	 */
	public function protocol($protocol)
	{
		include ROOT_PATH . 'lib/protocol/' . $protocol . '.php';
		
		return new $protocol();
	}
}
