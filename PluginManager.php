<?php

require_once('Plugin.php');

class PluginManager
{
	public static $plugins = array();
	
	public function __construct ()
	{
	}

	public function load ($name)
	{
		if (!class_exist($name.'Plugin'))
		{
			if (file_exists(EtkOS::get_profile().'/.eope/plugins/'.$name.'.php'))
			{
				require_once(EtkOS::get_profile().'/.eope/plugins/'.$name.'.php');
			}
			elseif (file_exists(EOPE_ROOT.'/Plugins/'.$name.'.php'))
			{
				require_once(EOPE_ROOT.'/Plugins/'.$name.'.php');
			}
			else
			{
				Etk::Error(__CLASS__, 'Cannot load '.$name.' plugin: file not found.');
				return;
			}
			
			if (!class_exists($name.'Plugin'))
			{
				Etk::Error(__CLASS__,'Cannot load '.$name.' plugin: class not found.');
				return;
			}
		}
		
		if (array_key_exists($name, self::$plugins))
		{
			Etk::Warning(__CLASS__, 'Plugin '.$name.' is already loaded.');
			return;
		}
		
		$plugin = $name.'Plugin';
		self::$plugins[$name] = new $plugin();
	}
	
	public function unload ($name)
	{
		if (!array_key_exists($name, self::$plugin))
		{
			Etk::Error(__CLASS__, 'Cannot unload '.$name.' plugin: the plugin is not loaded.');
		}
		self::$plugins[$name]->__destruct();
		unset(self::$plugins[$name]);
	}
	
	public function run_event ($event, $args = array())
	{
		if (!(bool)$event)
		{
			return;
		}
		$event = 'on_'.$event;
		foreach (self::$plugins as $plugin)
		{
			$plugin->$event($args);
		}
	}
}
