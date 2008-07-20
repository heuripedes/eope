<?php

abstract class Plugin
{
	
	public function on_application_startup ($args = array())
	{
	}
	
	public function on_application_shutdown ($args = array())
	{
	}
	
	public function on_main_window_create ($args = array())
	{
	}
	
	public function on_main_window_destroy ($args = array())
	{
	}
	
	public function on_file_load ($args = array())
	{
	}
	
	public function on_file_close ($args = array())
	{
	}
	
	public function on_configuration_load ($args = array())
	{
	}
	
	public function on_configuration_store ($args = array())
	{
	}
}
