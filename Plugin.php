<?php

abstract class Plugin
{
	protected $status = true;
	abstract public function get_handled_events ();
	
	public function get_status ()
	{
		return $this->status;
	}
	
	public function set_status ($ok = true)
	{
		$this->status = $ok;
	}
	
	public function __construct ()
	{
	}
	
	public function __destruct ()
	{
	}

}
