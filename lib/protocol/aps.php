<?php
include ROOT_PATH . 'lib/protocol/abstract.php';

class aps extends protocol_abstract
{
	private $__aps_version;
	private $__method;
	private $__extra = array();
	
	/**
	 * 
	 * @param unknown $content
	 */
	public function dealrecv($content)
	{
		var_dump($content);
		$this->__aps_version 	= $content[0];
		$this->__flag	 	= msgpack_unpack($content[1]);
		$this->__method		= $content[2];
		$this->__params		= msgpack_unpack($content[3]);
		
		if(isset($content[4]))
		{
			
			$this->__extra[] = msgpack_unpack($content[4]);
			if(isset($content[5]))
			{
				$this->__extra[] = msgpack_unpack($content[5]);
			}
		}
		var_dump($this->__aps_version);
		var_dump($this->__flag);
		var_dump($this->__method);
		var_dump($this->__params);
		var_dump($this->__extra);
		
	}
	
	/**
	 * 
	 * @param unknown $content
	 */
	public function dealreply()
	{
		
	}
}