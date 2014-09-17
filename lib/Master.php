<?php
/**
 * Created by PhpStorm.
 * User: wangxx
 * Date: 14-9-17
 * Time: 13:27
 */

class Master
{
    private $__proc_num = 1;
	private $__proc_pre = 'phpserver::';
	
    public function __construct()
    {
		// 注册自动加载函数
    	spl_autoload_register(array($this, '__autoload'));
    	$this->__set_proccess_name('master');
		
    }

    public function run($host)
    {
        $this->deamonize();
        $this->install_signo();
        $this->spawn_dispatch($host);
        $this->spawn_worker();
        $this->loop();
    }

    /**
     * 进程化
     */
    protected function deamonize()
    {

    }

    public function spawn_dispatch($host)
    {
        $pid = pcntl_fork();
        if($pid == 0)
        {
        	$this->__set_proccess_name('dispatch');
        	$dispatch_obj = new Proccess_Dispatch();
        	$dispatch_obj->task_dispatch($host);
        	
        }
    }

    public function spawn_worker()
    {
        for($i =0; $i < $this->__proc_num; $i++)
        {
            $pid = pcntl_fork();
            if($pid == 0)
            {
            	$this->__set_proccess_name('worker');
				$worker_obj = new Proccess_Worker();
				$worker_obj->task_worker();
            }
        }

    }
    /**
     *
     */
    protected function loop()
    {
    	while(true)
    	{
        	while(pcntl_waitpid(-1,$status, WNOHANG))
        	{
            	// 进程退出，创建新的进程
        	}
    	}
    }



    public function install_signo()
    {
        pcntl_signal(SIGPIPE, SIG_IGN);
        pcntl_signal(SIGCHLD, SIG_IGN);
    }

    public function sig_handler()
    {

    }
    
    private function __autoload($classname)
    {
    	if(strpos($classname, '_') !== false)
    	{
    		
    		$file = str_replace('_', DIRECTORY_SEPARATOR, $classname);
    		
    		require LIB_PATH . $file . '.php';
    	}
    }
    
    private function __set_proccess_name($name)
    {
    	cli_set_process_title($this->__proc_pre . $name);
    }
    

}