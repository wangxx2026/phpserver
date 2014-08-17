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
	
	private $__process_num = 10;
	
	private $__request_max = 1000;
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
		
		$this->server();
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
		$protocol = substr(stream_get_meta_data($fd), 0, 3);
		
		$this->set_process_name($protocol . 'master');
		
		for($i = 0; $i < $this->__process_num; ++$i)
		{
			$this->spawnworkder();
		}
		
		$event = event_new();
		
		event_set($event, $fd, EV_READ | EV_PERSIST, array($this, 'accept'), $server);
		
		event_base_set($event, $this->__base);
		event_add($event);
		
		event_base_loop($this->__base);
		
		echo '服务已关闭', PHP_EOL;
		exit();
		
	}
	/**
	 * 处理接收的数据
	 * @param unknown_type $fd
	 * @param unknown_type $events
	 * @param unknown_type $arg
	 * @todo 事件处理完成删除事件
	 */
	public function accept($fd, $events, $arg)
	{
		if($arg->getSockOpt(ZMQ::SOCKOPT_EVENTS))
		{
			$this->deal_data($arg);
			// 删除事件
		}
	}

	/**
	 * 数据处理
	 * @param unknown_type $server
	 */
	public function deal_data($server)
	{
		// 数据接收
		
		$content = $this->recv();
		// 数据发送
		$arg->send("Got msg $msgs");
	}
	/**
	 * 接收数据
	 * @param unknown_type $server
	 */
	public function recv($server)
	{
		$content = $server->recv();
		//缓存
		$this->cache($content);
		//处理协议
		//
		// 接收内容
	}
	
	public function send($server, $content)
	{
		$server->send($content);
	}
	
	public function cache()
	{
		
	}
	
	public function spawnworkder()
	{
		$pid = pcntl_fork();
		
		if($pid > 0)
		{
			return ;
		}
		$this->set_process_name('worker');
	}
	
	public function set_process_name($title)
	{
		if (function_exists('cli_set_process_title'))
		{
			@cli_set_process_title($title);
		}
	}
}
