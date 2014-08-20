<?php
/**
 * 
 * @author 星星
 *
 */

abstract class server_abstract
{
	abstract public function recv($server);
	abstract public function send($server, $content);
}
