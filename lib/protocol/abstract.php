<?php

abstract class protocol_abstract
{
	abstract public function dealrecv($content);
	abstract public function dealreply();
	
}