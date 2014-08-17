<?php
/**
 * 进程管理
 * @author 星星
 *
 */
class master
{
	
	public function __construct()
	{
		$this->daemon();
	}
	
	/**
	 * 守护进程化
	 */
	public function daemon()
	{
		$pid = pcntl_fork();
		//终止父进程
		if($pid > 0)
		{
			die();
		}
		
		$sid = posix_setsid();
		
		if($sid < 0)
		{
			die('setsid failed');
		}
	}
}