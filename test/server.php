<?php 

class server
{

	private $__event_base = NULL;
	
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

        
        /*$read = $write = array();
        //  Switch messages between frontend and backend
        while (true) {
            $poll = new ZMQPoll();
            $poll->add($frontend, ZMQ::POLL_IN);
            $poll->add($backend, ZMQ::POLL_IN);

            $poll->poll($read, $write);
            foreach ($read as $socket) {
                $content = $socket->recvMulti();
                if ($socket === $frontend) {
                    //echo "Request from client:";
                    //echo $zmsg->__toString();
                    $backend->sendMulti($content);
                } elseif ($socket === $backend) {
                    //echo "Request from worker:";
                    //echo $zmsg->__toString();
                    $frontend->sendMulti($content);
                }
            }
        }*/
		
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
			$content = $this->recv($args[0]);
			$content[2] .= '2';
			$res = $this->send($args[1], $content);
		}
	}
	
	public function send_msg($fd, $event, $arg)
	{
		echo 'CALLBACK FIRED2' . PHP_EOL;
		if($arg[0]->getSockOpt(ZMQ::SOCKOPT_EVENTS) & ZMQ::POLL_IN)
		{
			$content = $this->recv($arg[0]);
			$content[2] .= '3';
            var_dump($content);
			$this->send($arg[1], $content);
		}
	}
	
	public function server_worker()
	{
		
		$context = new ZMQContext();
		$worker = new ZMQSocket($context, ZMQ::SOCKET_DEALER);
		$worker->connect('ipc://backend');
		$fd_worker = $worker->getSockOpt(ZMQ::SOCKOPT_FD);

        //while (true) {
            //  The DEALER socket gives us the address envelope and message
            //$content = $worker->recvMulti();
            //var_dump($content);
            //$worker->sendMulti($content);
            // Send 0..4 replies back
            /*$replies = rand(0,4);
            for ($reply = 0; $reply < $replies; $reply++) {
                //  Sleep for some fraction of a second
                usleep(rand(0,1000) + 1);
                $zmsg->send(false);
            }*/

       // }
		
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
			$content = $this->recv($args);;
            $content[2] .= '4';
			var_dump($content);
			
			//var_dump($arg->getSockOpt(ZMQ::SOCKOPT_EVENTS));
			
			$res = $this->send($args, $content);
			//var_dump($res);
			
			/*foreach($content as $v)
			{
				$mode = ($i++ == $count) ? null : ZMQ::MODE_SNDMORE;
				$arg->sendMulti($content);
			}*/
		}
	}

    public function recv($socket)
    {
        $content = array();
        while(true)
        {
            $content[] = $socket->recv();
            $more = $socket->getSockOpt(ZMQ::SOCKOPT_RCVMORE);
            if(!$more)
            {
                break;
            }
        }
        return $content;
    }

    public function send($socket, $content)
    {
        $count = count($content);
        $i = 1;
        foreach($content as $val)
        {
            $mode = ($i++ == $count) ? NULL : ZMQ::MODE_SNDMORE;
            $socket->send($val, $mode);
        }
    }
}


new server('tcp://127.0.0.1:7001');

?>
