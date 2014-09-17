<?php
/**
 * 进程管理
 * @author 星星
 *
 */
class Proccess_Dispatch
{
    private $__event = NULL;
    public function __construct()
    {
        $this->__event = new Event_Event();
    }

    public function task_dispatch($host)
    {
        $context = new ZMQContext();
        $frontend = new ZMQSocket($context, ZMQ::SOCKET_ROUTER);
        $frontend->bind($host);
        $fd_frontend = $frontend->getSockOpt(ZMQ::SOCKOPT_FD);

        $backend = new ZMQSocket($context, ZMQ::SOCKET_DEALER);
        $backend->bind("ipc://backend");
        $fd_backend = $backend->getSockOpt(ZMQ::SOCKOPT_FD);

        $this->__event->add($fd_frontend, EV_READ | EV_PERSIST, array($this, 'accept'), array($frontend, $backend));

        $this->__event->add($fd_backend, EV_READ | EV_PERSIST, array($this, 'send'), array($backend, $frontend));
        
        $this->__event->loop();
    }
	
    
    public function accept($fd, $event, $args)
    {
    	echo 'CALLBACK FIRED' . PHP_EOL;
    	
    	if($args[0]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN)
    	{
    		$content = $args[0]->recvMulti();
    		$args[1]->sendMulti($content);
    	}
    }
    
    public function send($fd, $evnet, $args)
    {
    	echo 'CALLBACK FIRED2' . PHP_EOL;
    	if($args[0]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN)
    	{
    		$content = $args[0]->recvMulti();
    		$args[1]->sendMulti($content);
    	}
    	
    }

}
