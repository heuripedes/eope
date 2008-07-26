<?php

abstract class Plugin
{
	protected $status = true;	
	
	public function __construct ()
	{
	}
	
	public function __destruct ()
	{
	}
	
	public function get_status ()
	{
		return $this->status;
	}
	
	public function set_status ($ok = true)
	{
		$this->status = $ok;
	}
	
	public function add_to_panel ($panel, $widget)
	{
		if ($panel == 'left')
		{
		}
	}
	
	abstract public function get_handled_events ();
}
