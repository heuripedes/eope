<?php

class StatusIconPlugin extends Plugin
{
	protected static $status_icon = null;
	
	public function __construct ()
	{
		if (!class_exists('GtkStatusIcon'))
		{
			$this->set_status(false);
		}
	}
	
	public function get_handled_events ()
	{
		return array('main_window_create', 'application_terminate');
	}
	
	public function on_main_window_create ($window)
	{
		self::$status_icon = new GtkStatusIcon();
		self::$status_icon->set_from_pixbuf($window->get_icon());
		self::$status_icon->set_visible(true);
	
	}
	
	public function on_application_terminate ()
	{
		self::$status_icon->set_visible(false);
	}
}

