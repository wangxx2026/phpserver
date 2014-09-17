<?php
/**
 * Created by PhpStorm.
 * User: wangxx
 * Date: 14-9-17
 * Time: 14:03
 */
class Event_Event
{
	private $__event_base = NULL;
	private $__all_event = array();
	
	public function __construct()
	{
		$this->__event_base = event_base_new();
	}
	
    public function add($fd, $flag, $callback, $arg = null)
    {
    	$event_key = (int)$fd;
    	$event = event_new();
    	event_set($event, $fd, $flag, $callback, $arg);
    	event_base_set($event, $this->__event_base);
    	event_add($event);
    	$this->__all_event[$event_key][$flag] = $event;
    }

    public function del($fd, $flag)
    {
    	$event_key = (int)$fd;
    	event_del($this->__all_event[$event_key][$flag]);
        unset($this->__all_event[$event_key][$flag]);
    }

    public function loop()
    {
		event_base_loop($this->__event_base);
    }
}
